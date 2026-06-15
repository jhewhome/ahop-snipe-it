<?php



namespace App\Http\Controllers;



use App\Models\BillingInvoice;

use App\Models\LabOrder;

use App\Models\OpdVisit;

use App\Models\Patient;

use App\Services\ClinicSiteService;

use App\Services\OpdVisitInvoiceService;

use Carbon\Carbon;

use Illuminate\Contracts\View\View;

use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Gate;



class OpdVisitsController extends Controller

{

    public function __construct(

        protected OpdVisitInvoiceService $invoiceService,

        protected ClinicSiteService $clinicSiteService,

    ) {

        $this->middleware('auth');

        parent::__construct();

    }



    public function index(Request $request): View

    {

        $this->authorize('index', OpdVisit::class);



        $query = OpdVisit::query()

            ->with(['patient', 'physician'])

            ->orderByDesc('visit_date');



        if ($request->filled('search')) {

            $search = $request->input('search');

            $query->where(function ($q) use ($search) {

                $q->where('visit_number', 'like', '%'.$search.'%')

                    ->orWhere('chief_complaint', 'like', '%'.$search.'%')

                    ->orWhere('diagnosis', 'like', '%'.$search.'%')

                    ->orWhereHas('patient', function ($patientQuery) use ($search) {

                        $patientQuery->where('full_name', 'like', '%'.$search.'%')

                            ->orWhere('patient_number', 'like', '%'.$search.'%');

                    });

            });

        }



        if ($request->filled('status')) {

            $query->where('status', $request->input('status'));

        }



        if ($request->filled('patient_id')) {

            $query->where('patient_id', $request->integer('patient_id'));

        }



        $visits = $query->paginate(25)->withQueryString();



        return view('opd_visits.index', compact('visits'));

    }



    public function queue(Request $request): View

    {

        $this->authorize('index', OpdVisit::class);



        $day = $request->filled('date')

            ? Carbon::parse($request->input('date'))->startOfDay()

            : Carbon::today();



        $queue = OpdVisit::query()

            ->with(['patient', 'physician'])

            ->whereBetween('visit_date', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])

            ->whereIn('status', [OpdVisit::STATUS_SCHEDULED, OpdVisit::STATUS_IN_PROGRESS])

            ->orderBy('visit_date')

            ->get();



        return view('opd_visits.queue', compact('queue', 'day'));

    }



    public function create(Request $request): View

    {

        $this->authorize('create', OpdVisit::class);



        $item = new OpdVisit;

        $item->visit_number = OpdVisit::generateNextVisitNumber();

        $item->visit_date = now();

        $item->status = OpdVisit::STATUS_SCHEDULED;

        $item->visit_type = OpdVisit::TYPE_INITIAL;



        if ($request->filled('patient_id')) {

            $item->patient_id = $request->integer('patient_id');

            $patient = Patient::query()->find($item->patient_id);

            $item->company_id = $this->clinicSiteService->resolve(null, $patient);

        } else {

            $item->company_id = $this->clinicSiteService->resolve();

        }



        $patients = Patient::query()->orderBy('full_name')->get(['id', 'patient_number', 'full_name', 'allergies', 'problem_list']);

        $patientClinicalMap = $this->patientClinicalMap($patients);



        return view('opd_visits.edit', compact('item', 'patients', 'patientClinicalMap'));

    }



    public function store(Request $request): RedirectResponse

    {

        $this->authorize('create', OpdVisit::class);



        $visit = new OpdVisit;

        $visit->fill($this->visitAttributesFromRequest($request));

        $visit->created_by = auth()->id();

        $patient = Patient::query()->find($request->integer('patient_id'));

        $visit->company_id = $this->clinicSiteService->resolveFromRequest($request->input('company_id'), $patient);



        if ($visit->save()) {

            return redirect()->route('opd-visits.show', $visit)->with('success', trans('admin/opd_visits/message.create.success'));

        }



        return redirect()->back()->withInput()->withErrors($visit->getErrors());

    }



    public function show(OpdVisit $opd_visit): View

    {

        $this->authorize('view', $opd_visit);



        $opd_visit->load(['patient', 'physician', 'activeBillingInvoice', 'labOrders', 'company']);



        return view('opd_visits.view', ['visit' => $opd_visit]);

    }



    public function medicalCertificate(OpdVisit $opd_visit): View

    {

        $this->authorize('view', $opd_visit);



        $opd_visit->load(['patient', 'physician', 'company']);



        return view('opd_visits.medical-certificate', ['visit' => $opd_visit]);

    }



    public function createBillingInvoice(OpdVisit $opd_visit): RedirectResponse

    {

        $this->authorize('create', BillingInvoice::class);

        $this->authorize('view', $opd_visit);



        try {

            $result = $this->invoiceService->createOrOpenForVisit($opd_visit, auth()->id());

            $invoice = $result['invoice'];



            $message = $result['created']

                ? trans('admin/opd_visits/message.billing.created')

                : trans('admin/opd_visits/message.billing.opened');



            return redirect()

                ->route('billing-invoices.show', $invoice)

                ->with('success', $message);

        } catch (\Throwable $e) {

            return redirect()

                ->route('opd-visits.show', $opd_visit)

                ->with('error', $e->getMessage());

        }

    }



    public function storeLabOrder(Request $request, OpdVisit $opd_visit): RedirectResponse

    {

        $this->authorize('view', $opd_visit);

        $this->authorize('create', LabOrder::class);



        $panels = array_keys(LabOrder::testPanelOptions());

        $request->validate([

            'test_panel' => 'required|in:'.implode(',', $panels),

            'priority' => 'nullable|in:routine,urgent',

        ]);



        $order = new LabOrder;

        $order->order_number = LabOrder::generateNextOrderNumber();

        $order->patient_id = $opd_visit->patient_id;

        $order->opd_visit_id = $opd_visit->id;

        $order->ordered_by = auth()->id();

        $order->created_by = auth()->id();

        $order->test_panel = $request->input('test_panel');

        $order->status = LabOrder::STATUS_ORDERED;

        $order->priority = $request->input('priority', 'routine');

        $order->ordered_at = now();

        $order->clinical_notes = trans('admin/opd_visits/table.lab_from_visit', ['visit' => $opd_visit->visit_number]);

        $order->company_id = $opd_visit->company_id;



        if ($order->save()) {

            return redirect()

                ->route('opd-visits.show', $opd_visit)

                ->with('success', trans('admin/opd_visits/message.lab_order.created', [

                    'panel' => LabOrder::testPanelOptions()[$order->test_panel] ?? $order->test_panel,

                ]));

        }



        return redirect()

            ->route('opd-visits.show', $opd_visit)

            ->with('error', trans('admin/opd_visits/message.lab_order.error'));

    }



    public function updateStatus(Request $request, OpdVisit $opd_visit): RedirectResponse

    {

        $this->authorize('update', $opd_visit);



        $request->validate([

            'status' => 'required|in:'.implode(',', [

                OpdVisit::STATUS_SCHEDULED,

                OpdVisit::STATUS_IN_PROGRESS,

                OpdVisit::STATUS_COMPLETED,

                OpdVisit::STATUS_CANCELLED,

            ]),

        ]);



        $previousStatus = $opd_visit->status;

        $opd_visit->status = $request->input('status');



        if ($opd_visit->save()) {

            $message = trans('admin/opd_visits/message.status.updated');



            if (

                config('ahop.auto_bill_on_opd_complete', true)

                && $previousStatus !== OpdVisit::STATUS_COMPLETED

                && $opd_visit->status === OpdVisit::STATUS_COMPLETED

                && Gate::allows('create', BillingInvoice::class)

            ) {

                try {

                    $result = $this->invoiceService->createOrOpenForVisit($opd_visit, auth()->id());

                    if ($result['created']) {

                        $message .= ' '.trans('admin/opd_visits/message.billing.auto_created');

                    }

                } catch (\Throwable $e) {

                    $message .= ' '.trans('admin/opd_visits/message.billing.auto_failed', ['error' => $e->getMessage()]);

                }

            }



            return redirect()->back()->with('success', $message);

        }



        return redirect()->back()->with('error', trans('admin/opd_visits/message.update.error'));

    }



    public function edit(OpdVisit $opd_visit): View

    {

        $this->authorize('update', $opd_visit);



        $item = $opd_visit;

        $patients = Patient::query()->orderBy('full_name')->get(['id', 'patient_number', 'full_name', 'allergies', 'problem_list']);

        $patientClinicalMap = $this->patientClinicalMap($patients);



        return view('opd_visits.edit', compact('item', 'patients', 'patientClinicalMap'));

    }



    public function update(Request $request, OpdVisit $opd_visit): RedirectResponse

    {

        $this->authorize('update', $opd_visit);



        $previousStatus = $opd_visit->status;

        $opd_visit->fill($this->visitAttributesFromRequest($request));

        $patient = Patient::query()->find($request->integer('patient_id'));

        $opd_visit->company_id = $this->clinicSiteService->resolveFromRequest($request->input('company_id'), $patient)
            ?? $opd_visit->company_id;



        if ($opd_visit->save()) {

            $message = trans('admin/opd_visits/message.update.success');



            if (

                config('ahop.auto_bill_on_opd_complete', true)

                && $previousStatus !== OpdVisit::STATUS_COMPLETED

                && $opd_visit->status === OpdVisit::STATUS_COMPLETED

                && Gate::allows('create', BillingInvoice::class)

            ) {

                try {

                    $result = $this->invoiceService->createOrOpenForVisit($opd_visit, auth()->id());

                    if ($result['created']) {

                        $message .= ' '.trans('admin/opd_visits/message.billing.auto_created');

                    }

                } catch (\Throwable $e) {

                    $message .= ' '.trans('admin/opd_visits/message.billing.auto_failed', ['error' => $e->getMessage()]);

                }

            }



            return redirect()->route('opd-visits.show', $opd_visit)->with('success', $message);

        }



        return redirect()->back()->withInput()->withErrors($opd_visit->getErrors());

    }



    public function destroy(OpdVisit $opd_visit): RedirectResponse

    {

        $this->authorize('delete', $opd_visit);



        if ($opd_visit->delete()) {

            return redirect()->route('opd-visits.index')->with('success', trans('admin/opd_visits/message.delete.success'));

        }



        return redirect()->back()->with('error', trans('admin/opd_visits/message.delete.error'));

    }



    /**

     * @param  \Illuminate\Support\Collection<int, Patient>|\Illuminate\Database\Eloquent\Collection<int, Patient>  $patients

     * @return array<int|string, array{allergies: ?string, problem_list: ?string}>

     */

    protected function patientClinicalMap($patients): array

    {

        return $patients->mapWithKeys(fn (Patient $patient) => [

            $patient->id => [

                'allergies' => $patient->allergies,

                'problem_list' => $patient->problem_list,

            ],

        ])->all();

    }



    private function visitAttributesFromRequest(Request $request): array

    {

        $data = $request->only([

            'visit_number',

            'patient_id',

            'physician_id',

            'visit_date',

            'visit_type',

            'status',

            'chief_complaint',

            'blood_pressure',

            'pulse_rate',

            'temperature',

            'weight_kg',

            'height_cm',

            'assessment',

            'diagnosis',

            'rest_days',

            'med_cert_remarks',

        ]);

        if (($data['rest_days'] ?? '') === '' || $data['rest_days'] === null) {

            $data['rest_days'] = null;

        }

        if (($data['med_cert_remarks'] ?? '') === '') {

            $data['med_cert_remarks'] = null;

        }

        return $data;

    }

}


