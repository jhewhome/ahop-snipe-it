<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Services\Ahop\PatientRiskPredictor;
use App\Services\Ahop\PatientTimelineBuilder;
use App\Services\ClinicSiteService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientsController extends Controller
{
    public function __construct(
        protected ClinicSiteService $clinicSiteService,
    ) {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $request): View
    {
        $this->authorize('index', Patient::class);

        $query = Patient::query()->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%'.$search.'%')
                    ->orWhere('patient_number', 'like', '%'.$search.'%')
                    ->orWhere('contact_number', 'like', '%'.$search.'%');
            });
        }

        $patients = $query->paginate(25)->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create(): View
    {
        $this->authorize('create', Patient::class);

        $item = new Patient;
        $item->patient_number = Patient::generateNextPatientNumber();
        $item->company_id = $this->clinicSiteService->ensureSessionSite();

        return view('patients.edit', compact('item'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Patient::class);

        $patient = new Patient;
        $patient->fill($this->patientAttributesFromRequest($request));
        $patient->created_by = auth()->id();
        $patient->company_id = $this->clinicSiteService->resolveFromRequest($request->input('company_id'));

        if ($patient->save()) {
            return redirect()->route('patients.index')->with('success', trans('admin/patients/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($patient->getErrors());
    }

    public function show(Patient $patient): View
    {
        $this->authorize('view', $patient);

        $patient->load([
            'company',
            'opdVisits' => fn ($q) => $q->with('physician')->limit(25),
            'appointments' => fn ($q) => $q->with('physician')->limit(25),
            'labOrders' => fn ($q) => $q->withCount('results')->limit(25),
            'billingInvoices' => fn ($q) => $q->limit(25),
        ]);

        $patientRisk = null;
        if (config('ahop.clinical_analytics_enabled', config('ahop.ai_insights_enabled')) && Gate::allows('ai_insights.view')) {
            $patientRisk = app(PatientRiskPredictor::class)->assess($patient);
        }

        $timeline = app(PatientTimelineBuilder::class)->build($patient);

        return view('patients.view', compact('patient', 'patientRisk', 'timeline'));
    }

    public function edit(Patient $patient): View
    {
        $this->authorize('update', $patient);

        return view('patients.edit', ['item' => $patient]);
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('update', $patient);

        $patient->fill($this->patientAttributesFromRequest($request));
        $patient->company_id = $this->clinicSiteService->resolveFromRequest($request->input('company_id'));

        if ($patient->save()) {
            return redirect()->route('patients.show', $patient)->with('success', trans('admin/patients/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($patient->getErrors());
    }

    public function clinicalSummary(Patient $patient): View
    {
        $this->authorize('view', $patient);

        $patient->load([
            'opdVisits' => fn ($q) => $q->with('physician')->orderByDesc('visit_date')->limit(10),
            'labOrders' => fn ($q) => $q->withCount('results')->orderByDesc('ordered_at')->limit(10),
            'billingInvoices' => fn ($q) => $q->orderByDesc('issued_at')->limit(10),
        ]);

        return view('patients.clinical-summary', compact('patient'));
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $this->authorize('delete', $patient);

        if ($patient->delete()) {
            return redirect()->route('patients.index')->with('success', trans('admin/patients/message.delete.success'));
        }

        return redirect()->back()->with('error', trans('admin/patients/message.delete.error'));
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
            'notes',
        ]);

        if ($data['email'] === '' || $data['email'] === null) {
            $data['email'] = null;
        }

        return $data;
    }
}
