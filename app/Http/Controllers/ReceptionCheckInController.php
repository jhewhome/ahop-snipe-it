<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Company;
use App\Models\OpdVisit;
use App\Models\Patient;
use App\Services\AppointmentCheckInService;
use App\Services\ClinicSiteService;
use App\Services\PhysicianSelectService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReceptionCheckInController extends Controller
{
    public function __construct(
        protected AppointmentCheckInService $checkInService,
        protected ClinicSiteService $clinicSiteService,
    ) {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $request): View|RedirectResponse
    {
        $this->authorizeReceptionAccess();

        if ($request->filled('clinic_site_id')) {
            $this->clinicSiteService->setSessionSite($request->integer('clinic_site_id'));

            return redirect()->route('reception.check-in', $request->except('clinic_site_id'));
        }

        $clinicSites = $this->clinicSiteService->availableSites();
        $activeClinicSiteId = $this->clinicSiteService->ensureSessionSite();
        $activeClinicSiteName = $this->clinicSiteService->siteName($activeClinicSiteId);

        $searchQuery = trim((string) $request->input('search', ''));
        $searchResults = collect();
        if ($searchQuery !== '') {
            $searchResults = $this->patientSearchQuery($searchQuery)->limit(20)->get();
        }

        $selectedPatient = null;
        $patientAppointments = collect();
        $activeVisit = null;

        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::query()->with('company')->find($request->integer('patient_id'));

            if ($selectedPatient) {
                $this->authorize('view', $selectedPatient);

                $patientAppointments = Appointment::query()
                    ->with('physician')
                    ->where('patient_id', $selectedPatient->id)
                    ->whereBetween('scheduled_at', [now()->startOfDay(), now()->endOfDay()])
                    ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CHECKED_IN])
                    ->orderBy('scheduled_at')
                    ->get();

                $activeVisit = OpdVisit::query()
                    ->with('company')
                    ->where('patient_id', $selectedPatient->id)
                    ->whereBetween('visit_date', [now()->startOfDay(), now()->endOfDay()])
                    ->where('status', OpdVisit::STATUS_IN_PROGRESS)
                    ->first();
            }
        }

        $todayQueue = Appointment::query()
            ->with(['patient', 'physician'])
            ->whereBetween('scheduled_at', [now()->startOfDay(), now()->endOfDay()])
            ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CHECKED_IN])
            ->orderBy('scheduled_at')
            ->get();

        $opdQueueCount = OpdVisit::query()
            ->whereBetween('visit_date', [now()->startOfDay(), now()->endOfDay()])
            ->whereIn('status', [OpdVisit::STATUS_SCHEDULED, OpdVisit::STATUS_IN_PROGRESS])
            ->count();

        $nextPatientNumber = Patient::generateNextPatientNumber();
        $nextVisitNumber = OpdVisit::generateNextVisitNumber();
        $canSelectClinicSite = Company::canManageUsersCompanies() && $clinicSites->count() > 1;
        $physicians = PhysicianSelectService::roster(PhysicianSelectService::defaultPhysicianId());
        $defaultPhysicianId = PhysicianSelectService::defaultPhysicianId();

        return view('reception.check-in', compact(
            'searchResults',
            'selectedPatient',
            'patientAppointments',
            'activeVisit',
            'todayQueue',
            'opdQueueCount',
            'nextPatientNumber',
            'nextVisitNumber',
            'clinicSites',
            'activeClinicSiteId',
            'activeClinicSiteName',
            'canSelectClinicSite',
            'searchQuery',
            'physicians',
            'defaultPhysicianId',
        ));
    }

    public function searchPatients(Request $request): JsonResponse
    {
        $this->authorizeReceptionAccess();

        $search = trim((string) $request->input('q', ''));

        if (mb_strlen($search) < 2) {
            return response()->json([
                'results' => [],
                'html' => '',
                'message' => trans('admin/reception/table.type_to_search'),
            ]);
        }

        $searchResults = $this->patientSearchQuery($search)->limit(20)->get();

        $results = $searchResults->map(fn (Patient $patient) => [
            'id' => $patient->id,
            'patient_number' => $patient->patient_number,
            'full_name' => $patient->full_name,
            'contact_number' => $patient->contact_number,
            'url' => route('reception.check-in', ['patient_id' => $patient->id]),
        ])->values();

        return response()->json([
            'results' => $results,
            'html' => view('reception.partials.patient-search-results', [
                'searchResults' => $searchResults,
                'search' => $search,
            ])->render(),
        ]);
    }

    public function storePatient(Request $request): RedirectResponse
    {
        $this->authorize('create', Patient::class);

        $patient = new Patient;
        $patient->fill($this->patientAttributesFromRequest($request));
        $patient->created_by = auth()->id();
        $patient->company_id = $this->clinicSiteService->resolveFromRequest($request->input('company_id'));

        if ($patient->save()) {
            return redirect()
                ->route('reception.check-in', ['patient_id' => $patient->id])
                ->with('success', trans('admin/reception/message.patient_registered'));
        }

        return redirect()
            ->route('reception.check-in')
            ->withInput()
            ->withErrors($patient->getErrors());
    }

    public function checkInAppointment(Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        try {
            $visit = $this->checkInService->checkIn($appointment);

            return redirect()
                ->route('reception.check-in', ['patient_id' => $appointment->patient_id])
                ->with('success', trans('admin/reception/message.appointment_checked_in', [
                    'visit' => $visit->visit_number,
                ]));
        } catch (\Throwable $e) {
            return redirect()
                ->route('reception.check-in', ['patient_id' => $appointment->patient_id])
                ->with('error', $e->getMessage());
        }
    }

    public function storeWalkIn(Request $request): RedirectResponse
    {
        $this->authorize('create', OpdVisit::class);

        $patientId = $request->integer('patient_id');
        $patient = Patient::query()->findOrFail($patientId);
        $this->authorize('view', $patient);

        $existingVisit = OpdVisit::query()
            ->where('patient_id', $patientId)
            ->whereBetween('visit_date', [now()->startOfDay(), now()->endOfDay()])
            ->where('status', OpdVisit::STATUS_IN_PROGRESS)
            ->first();

        if ($existingVisit) {
            return redirect()
                ->route('reception.check-in', ['patient_id' => $patientId])
                ->with('error', trans('admin/reception/message.already_in_progress', [
                    'visit' => $existingVisit->visit_number,
                ]));
        }

        $visit = new OpdVisit;
        $visit->visit_number = OpdVisit::generateNextVisitNumber();
        $visit->patient_id = $patientId;
        $visit->physician_id = $request->input('physician_id') ?: null;
        $visit->visit_date = now();
        $visit->visit_type = OpdVisit::TYPE_WALK_IN;
        $visit->status = OpdVisit::STATUS_IN_PROGRESS;
        $visit->chief_complaint = $request->input('chief_complaint');
        $visit->created_by = auth()->id();
        $visit->company_id = $this->clinicSiteService->resolveFromRequest($request->input('company_id'), $patient);

        if ($visit->company_id && ! $patient->company_id) {
            $patient->company_id = $visit->company_id;
            $patient->saveQuietly();
        }

        if ($visit->save()) {
            return redirect()
                ->route('reception.check-in', ['patient_id' => $patientId])
                ->with('success', trans('admin/reception/message.walk_in_checked_in', [
                    'visit' => $visit->visit_number,
                ]));
        }

        return redirect()
            ->route('reception.check-in', ['patient_id' => $patientId])
            ->withInput()
            ->withErrors($visit->getErrors());
    }

    private function authorizeReceptionAccess(): void
    {
        if (! Gate::allows('view', Patient::class)) {
            abort(403);
        }

        if (! Gate::allows('create', OpdVisit::class) && ! Gate::allows('index', Appointment::class)) {
            abort(403);
        }
    }

    private function patientAttributesFromRequest(Request $request): array
    {
        $data = $request->only([
            'patient_number',
            'full_name',
            'sex',
            'birthdate',
            'contact_number',
            'email',
            'allergies',
            'problem_list',
        ]);

        if (($data['email'] ?? null) === '') {
            $data['email'] = null;
        }

        return $data;
    }

    private function patientSearchQuery(string $search)
    {
        return Patient::query()
            ->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%'.$search.'%')
                    ->orWhere('patient_number', 'like', '%'.$search.'%')
                    ->orWhere('contact_number', 'like', '%'.$search.'%');
            })
            ->orderBy('full_name');
    }
}
