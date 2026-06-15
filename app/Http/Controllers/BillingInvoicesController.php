<?php

namespace App\Http\Controllers;

use App\Models\BillableService;
use App\Models\BillingInvoice;
use App\Models\BillingLineItem;
use App\Models\BillingPayment;
use App\Models\Company;
use App\Models\OpdVisit;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BillingInvoicesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $request): View
    {
        $this->authorize('index', BillingInvoice::class);

        $query = BillingInvoice::query()
            ->with('patient')
            ->orderByDesc('issued_at')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', '%'.$search.'%')
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

        $invoices = $query->paginate(25)->withQueryString();

        $todayCollections = BillingPayment::query()
            ->whereDate('paid_at', Carbon::today())
            ->sum('amount');

        return view('billing_invoices.index', compact('invoices', 'todayCollections'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', BillingInvoice::class);

        $item = new BillingInvoice;
        $item->invoice_number = BillingInvoice::generateNextInvoiceNumber();
        $item->status = BillingInvoice::STATUS_DRAFT;
        $item->issued_at = now();

        if ($request->filled('patient_id')) {
            $item->patient_id = $request->integer('patient_id');
        }

        if ($request->filled('opd_visit_id')) {
            $item->opd_visit_id = $request->integer('opd_visit_id');
        }

        if ($request->filled('appointment_id')) {
            $item->appointment_id = $request->integer('appointment_id');
        }

        $patients = Patient::query()->orderBy('full_name')->get(['id', 'patient_number', 'full_name']);

        return view('billing_invoices.edit', compact('item', 'patients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', BillingInvoice::class);

        $invoice = new BillingInvoice;
        $invoice->fill($this->invoiceAttributesFromRequest($request, isNew: true));
        $invoice->created_by = auth()->id();
        $invoice->company_id = Company::getIdForCurrentUser($request->input('company_id'));

        if ($invoice->save()) {
            if ($request->boolean('issue_now')) {
                $invoice->issue();
            }

            return redirect()->route('billing-invoices.show', $invoice)->with('success', trans('admin/billing_invoices/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($invoice->getErrors());
    }

    public function show(BillingInvoice $billing_invoice): View
    {
        $this->authorize('view', $billing_invoice);

        $billing_invoice->load(['patient', 'opdVisit', 'appointment', 'lineItems.billableService', 'payments.receivedByUser']);
        $services = BillableService::activeCatalog();

        return view('billing_invoices.view', [
            'invoice' => $billing_invoice,
            'services' => $services,
        ]);
    }

    public function edit(BillingInvoice $billing_invoice): View
    {
        $this->authorize('update', $billing_invoice);

        $item = $billing_invoice;
        $patients = Patient::query()->orderBy('full_name')->get(['id', 'patient_number', 'full_name']);

        return view('billing_invoices.edit', compact('item', 'patients'));
    }

    public function update(Request $request, BillingInvoice $billing_invoice): RedirectResponse
    {
        $this->authorize('update', $billing_invoice);

        $billing_invoice->fill($this->invoiceAttributesFromRequest($request, isNew: false));
        $billing_invoice->company_id = Company::getIdForCurrentUser($request->input('company_id'));

        if ($billing_invoice->save()) {
            if ($request->boolean('issue_now') && $billing_invoice->status === BillingInvoice::STATUS_DRAFT) {
                $billing_invoice->issue();
            } else {
                $billing_invoice->recalculateTotals();
            }

            return redirect()->route('billing-invoices.show', $billing_invoice)->with('success', trans('admin/billing_invoices/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($billing_invoice->getErrors());
    }

    public function destroy(BillingInvoice $billing_invoice): RedirectResponse
    {
        $this->authorize('delete', $billing_invoice);

        if ($billing_invoice->delete()) {
            return redirect()->route('billing-invoices.index')->with('success', trans('admin/billing_invoices/message.delete.success'));
        }

        return redirect()->back()->with('error', trans('admin/billing_invoices/message.delete.error'));
    }

    public function storeLineItem(Request $request, BillingInvoice $billing_invoice): RedirectResponse
    {
        $this->authorize('update', $billing_invoice);

        if ($billing_invoice->status === BillingInvoice::STATUS_CANCELLED) {
            return redirect()->back()->with('error', trans('admin/billing_invoices/message.cancelled_locked'));
        }

        $service = null;
        if ($request->filled('billable_service_id')) {
            $service = BillableService::find($request->integer('billable_service_id'));
        }

        $quantity = max(1, (int) $request->input('quantity', 1));
        $unitAmount = $request->filled('unit_amount')
            ? (float) $request->input('unit_amount')
            : (float) ($service?->default_amount ?? 0);

        $description = $request->input('description') ?: ($service?->name ?? trans('admin/billing_invoices/table.line_item'));

        $line = new BillingLineItem;
        $line->billing_invoice_id = $billing_invoice->id;
        $line->billable_service_id = $service?->id;
        $line->description = $description;
        $line->quantity = $quantity;
        $line->unit_amount = $unitAmount;
        $line->line_total = BillingLineItem::computeLineTotal($quantity, $unitAmount);

        if ($line->save()) {
            $billing_invoice->issue();

            return redirect()->route('billing-invoices.show', $billing_invoice)->with('success', trans('admin/billing_invoices/message.line_item.success'));
        }

        return redirect()->back()->withInput()->withErrors($line->getErrors());
    }

    public function destroyLineItem(BillingInvoice $billing_invoice, BillingLineItem $line_item): RedirectResponse
    {
        $this->authorize('update', $billing_invoice);

        if ($line_item->billing_invoice_id !== $billing_invoice->id) {
            abort(404);
        }

        $line_item->delete();
        $billing_invoice->recalculateTotals();

        return redirect()->route('billing-invoices.show', $billing_invoice)->with('success', trans('admin/billing_invoices/message.line_item.deleted'));
    }

    public function storePayment(Request $request, BillingInvoice $billing_invoice): RedirectResponse
    {
        $this->authorize('update', $billing_invoice);

        if ($billing_invoice->status === BillingInvoice::STATUS_CANCELLED) {
            return redirect()->back()->with('error', trans('admin/billing_invoices/message.cancelled_locked'));
        }

        $payment = new BillingPayment;
        $payment->billing_invoice_id = $billing_invoice->id;
        $payment->fill($request->only(['amount', 'payment_method', 'reference', 'notes']));
        $payment->paid_at = $request->input('paid_at', now());
        $payment->received_by = auth()->id();

        if ($payment->save()) {
            $billing_invoice->recalculateTotals();

            return redirect()->route('billing-invoices.show', $billing_invoice)->with('success', trans('admin/billing_invoices/message.payment.success'));
        }

        return redirect()->back()->withInput()->withErrors($payment->getErrors());
    }

    public function destroyPayment(BillingInvoice $billing_invoice, BillingPayment $payment): RedirectResponse
    {
        $this->authorize('update', $billing_invoice);

        if ($payment->billing_invoice_id !== $billing_invoice->id) {
            abort(404);
        }

        $payment->delete();
        $billing_invoice->recalculateTotals();

        return redirect()->route('billing-invoices.show', $billing_invoice)->with('success', trans('admin/billing_invoices/message.payment.deleted'));
    }

    public function receipt(BillingInvoice $billing_invoice): View
    {
        $this->authorize('view', $billing_invoice);

        $billing_invoice->load(['patient', 'lineItems', 'payments.receivedByUser']);

        return view('billing_invoices.receipt', ['invoice' => $billing_invoice]);
    }

    private function invoiceAttributesFromRequest(Request $request, bool $isNew = false): array
    {
        $data = $request->only([
            'invoice_number',
            'patient_id',
            'opd_visit_id',
            'appointment_id',
            'status',
            'issued_at',
            'notes',
        ]);

        if (empty($data['opd_visit_id'])) {
            $data['opd_visit_id'] = null;
        }

        if (empty($data['appointment_id'])) {
            $data['appointment_id'] = null;
        }

        if ($isNew && empty($data['status'])) {
            $data['status'] = BillingInvoice::STATUS_DRAFT;
        }

        return $data;
    }
}
