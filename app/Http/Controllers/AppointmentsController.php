<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BillingInvoice;
use App\Models\Company;
use App\Models\OpdVisit;
use App\Models\Patient;
use App\Services\AppointmentCheckInService;
use App\Services\AppointmentInvoiceService;
use App\Services\AppointmentReminderService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppointmentsController extends Controller
{
    public function __construct(
        protected AppointmentCheckInService $checkInService,
        protected AppointmentReminderService $reminderService,
        protected AppointmentInvoiceService $invoiceService
    ) {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $request): View
    {
        $this->authorize('index', Appointment::class);

        $day = $request->filled('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        $query = Appointment::query()
            ->with(['patient', 'physician', 'opdVisit'])
            ->orderBy('scheduled_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('appointment_number', 'like', '%'.$search.'%')
                    ->orWhere('reason', 'like', '%'.$search.'%')
                    ->orWhereHas('patient', function ($patientQuery) use ($search) {
                        $patientQuery->where('full_name', 'like', '%'.$search.'%')
                            ->orWhere('patient_number', 'like', '%'.$search.'%');
                    });
            });
        } else {
            $query->whereBetween('scheduled_at', [
                $day->copy()->startOfDay(),
                $day->copy()->endOfDay(),
            ]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $appointments = $query->paginate(50)->withQueryString();
        $todayQueue = Appointment::query()
            ->with(['patient', 'physician'])
            ->whereBetween('scheduled_at', [now()->startOfDay(), now()->endOfDay()])
            ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CHECKED_IN])
            ->orderBy('scheduled_at')
            ->get();

        return view('appointments.index', compact('appointments', 'todayQueue', 'day'));
    }

    public function calendar(Request $request): View
    {
        $this->authorize('index', Appointment::class);

        $weekStart = $request->filled('week')
            ? Carbon::parse($request->input('week'))->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $appointments = Appointment::query()
            ->with(['patient', 'physician'])
            ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy(fn (Appointment $a) => $a->scheduled_at->format('Y-m-d'));

        $days = [];
        for ($d = 0; $d < 7; $d++) {
            $date = $weekStart->copy()->addDays($d);
            $key = $date->format('Y-m-d');
            $days[$key] = [
                'date' => $date,
                'label' => $date->format('D, M j'),
                'appointments' => $appointments->get($key, collect()),
            ];
        }

        return view('appointments.calendar', compact('days', 'weekStart', 'weekEnd'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Appointment::class);

        $item = new Appointment;
        $item->appointment_number = Appointment::generateNextAppointmentNumber();
        $item->scheduled_at = $request->filled('scheduled_at')
            ? Carbon::parse($request->input('scheduled_at'))
            : now()->addHour()->startOfHour();
        $item->duration_minutes = 30;
        $item->status = Appointment::STATUS_SCHEDULED;
        $item->visit_type = OpdVisit::TYPE_INITIAL;

        if ($request->filled('patient_id')) {
            $item->patient_id = $request->integer('patient_id');
        }

        $patients = Patient::query()->orderBy('full_name')->get(['id', 'patient_number', 'full_name']);

        return view('appointments.edit', compact('item', 'patients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Appointment::class);

        $appointment = new Appointment;
        $attributes = $this->attributesFromRequest($request);
        $attributes['status'] = Appointment::STATUS_SCHEDULED;
        $appointment->fill($attributes);
        $appointment->created_by = auth()->id();
        $appointment->company_id = Company::getIdForCurrentUser($request->input('company_id'));

        if ($appointment->save()) {
            return redirect()->route('appointments.show', $appointment)
                ->with('success', trans('admin/appointments/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($appointment->getErrors());
    }

    public function show(Appointment $appointment): View
    {
        $this->authorize('view', $appointment);

        $appointment->load(['patient', 'physician', 'opdVisit.activeBillingInvoice', 'activeBillingInvoice']);

        return view('appointments.view', compact('appointment'));
    }

    public function createBillingInvoice(Appointment $appointment): RedirectResponse
    {
        $this->authorize('create', BillingInvoice::class);
        $this->authorize('view', $appointment);

        try {
            $result = $this->invoiceService->createOrOpenForAppointment($appointment, auth()->id());
            $invoice = $result['invoice'];

            $message = $result['created']
                ? trans('admin/appointments/message.billing.created')
                : trans('admin/appointments/message.billing.opened');

            return redirect()
                ->route('billing-invoices.show', $invoice)
                ->with('success', $message);
        } catch (\Throwable $e) {
            return redirect()
                ->route('appointments.show', $appointment)
                ->with('error', $e->getMessage());
        }
    }

    public function edit(Appointment $appointment): View
    {
        $this->authorize('update', $appointment);

        $item = $appointment;
        $patients = Patient::query()->orderBy('full_name')->get(['id', 'patient_number', 'full_name']);

        return view('appointments.edit', compact('item', 'patients'));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $allowedStatuses = array_keys($appointment->editableStatusOptions());
        $request->validate([
            'status' => 'required|in:'.implode(',', $allowedStatuses),
        ]);

        $appointment->fill($this->attributesFromRequest($request));
        $appointment->company_id = Company::getIdForCurrentUser($request->input('company_id'));

        if ($appointment->save()) {
            return redirect()->route('appointments.show', $appointment)
                ->with('success', trans('admin/appointments/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($appointment->getErrors());
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->authorize('delete', $appointment);

        if ($appointment->delete()) {
            return redirect()->route('appointments.index')
                ->with('success', trans('admin/appointments/message.delete.success'));
        }

        return redirect()->back()->with('error', trans('admin/appointments/message.delete.error'));
    }

    public function checkIn(Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        try {
            $visit = $this->checkInService->checkIn($appointment);

            return redirect()->route('opd-visits.show', $visit)
                ->with('success', trans('admin/appointments/message.check_in.success', [
                    'visit' => $visit->visit_number,
                ]));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function sendReminder(Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $result = $this->reminderService->sendReminder($appointment, force: true);

        if ($result['sent']) {
            return redirect()
                ->route('appointments.show', $appointment)
                ->with('success', trans('admin/appointments/message.reminder.sent'));
        }

        $key = match ($result['reason']) {
            'no_email' => 'no_email',
            'disabled' => 'disabled',
            'invalid_status' => 'invalid_status',
            default => 'failed',
        };

        return redirect()
            ->route('appointments.show', $appointment)
            ->with('error', trans('admin/appointments/message.reminder.'.$key));
    }

    private function attributesFromRequest(Request $request): array
    {
        return $request->only([
            'appointment_number',
            'patient_id',
            'physician_id',
            'scheduled_at',
            'duration_minutes',
            'visit_type',
            'status',
            'reason',
            'notes',
        ]);
    }
}
