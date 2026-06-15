<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\OpdVisit;
use App\Models\Patient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LabOrdersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $request): View
    {
        $this->authorize('index', LabOrder::class);

        $query = LabOrder::query()
            ->with(['patient', 'orderedByUser'])
            ->withCount('results')
            ->orderByDesc('ordered_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%'.$search.'%')
                    ->orWhere('test_panel', 'like', '%'.$search.'%')
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

        $labOrders = $query->paginate(25)->withQueryString();

        return view('lab_orders.index', compact('labOrders'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', LabOrder::class);

        $item = new LabOrder;
        $item->order_number = LabOrder::generateNextOrderNumber();
        $item->ordered_at = now();
        $item->status = LabOrder::STATUS_ORDERED;
        $item->priority = 'routine';
        $item->ordered_by = auth()->id();

        if ($request->filled('patient_id')) {
            $item->patient_id = $request->integer('patient_id');
        }

        if ($request->filled('opd_visit_id')) {
            $item->opd_visit_id = $request->integer('opd_visit_id');
            $item->load('opdVisit');
        }

        $patients = Patient::query()->orderBy('full_name')->get(['id', 'patient_number', 'full_name']);

        return view('lab_orders.edit', compact('item', 'patients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', LabOrder::class);

        $order = new LabOrder;
        $order->fill($this->orderAttributesFromRequest($request));
        $order->created_by = auth()->id();
        $order->company_id = Company::getIdForCurrentUser($request->input('company_id'));

        if (! $order->ordered_by) {
            $order->ordered_by = auth()->id();
        }

        if ($order->save()) {
            if ($order->opd_visit_id) {
                return redirect()
                    ->route('opd-visits.show', $order->opd_visit_id)
                    ->with('success', trans('admin/lab_orders/message.create.success'));
            }

            return redirect()->route('lab-orders.show', $order)->with('success', trans('admin/lab_orders/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($order->getErrors());
    }

    public function show(LabOrder $lab_order): View
    {
        $this->authorize('view', $lab_order);

        $lab_order->load(['patient', 'opdVisit', 'orderedByUser', 'results']);

        return view('lab_orders.view', ['order' => $lab_order]);
    }

    public function edit(LabOrder $lab_order): View
    {
        $this->authorize('update', $lab_order);

        $item = $lab_order;
        $item->load('opdVisit');
        $patients = Patient::query()->orderBy('full_name')->get(['id', 'patient_number', 'full_name']);

        return view('lab_orders.edit', compact('item', 'patients'));
    }

    public function update(Request $request, LabOrder $lab_order): RedirectResponse
    {
        $this->authorize('update', $lab_order);

        $lab_order->fill($this->orderAttributesFromRequest($request));
        $lab_order->company_id = Company::getIdForCurrentUser($request->input('company_id'));

        if ($lab_order->status === LabOrder::STATUS_COMPLETED && ! $lab_order->completed_at) {
            $lab_order->completed_at = now();
        }

        if ($lab_order->save()) {
            return redirect()->route('lab-orders.show', $lab_order)->with('success', trans('admin/lab_orders/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($lab_order->getErrors());
    }

    public function destroy(LabOrder $lab_order): RedirectResponse
    {
        $this->authorize('delete', $lab_order);

        if ($lab_order->delete()) {
            return redirect()->route('lab-orders.index')->with('success', trans('admin/lab_orders/message.delete.success'));
        }

        return redirect()->back()->with('error', trans('admin/lab_orders/message.delete.error'));
    }

    public function storeResult(Request $request, LabOrder $lab_order): RedirectResponse
    {
        $this->authorize('update', $lab_order);

        $result = new LabResult;
        $result->lab_order_id = $lab_order->id;
        $result->fill($request->only([
            'test_code',
            'test_name',
            'result_value',
            'unit',
            'reference_range',
            'flag',
            'notes',
        ]));
        $result->result_at = $request->input('result_at', now());

        if ($result->save()) {
            if ($lab_order->status === LabOrder::STATUS_ORDERED) {
                $lab_order->status = LabOrder::STATUS_IN_PROGRESS;
                $lab_order->save();
            }
            $lab_order->markCompletedIfHasResults();

            return redirect()->route('lab-orders.show', $lab_order)->with('success', trans('admin/lab_orders/message.result.success'));
        }

        return redirect()->back()->withInput()->withErrors($result->getErrors());
    }

    public function destroyResult(LabOrder $lab_order, LabResult $result): RedirectResponse
    {
        $this->authorize('update', $lab_order);

        if ($result->lab_order_id !== $lab_order->id) {
            abort(404);
        }

        $result->delete();

        return redirect()->route('lab-orders.show', $lab_order)->with('success', trans('admin/lab_orders/message.result.deleted'));
    }

    private function orderAttributesFromRequest(Request $request): array
    {
        return $request->only([
            'order_number',
            'patient_id',
            'opd_visit_id',
            'ordered_by',
            'test_panel',
            'status',
            'priority',
            'clinical_notes',
            'ordered_at',
            'completed_at',
        ]);
    }
}
