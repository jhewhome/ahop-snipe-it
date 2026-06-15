<?php

namespace App\Services;

use App\Models\BillableService;
use App\Models\BillingInvoice;
use App\Models\BillingLineItem;
use App\Models\OpdVisit;
use Illuminate\Support\Facades\DB;

class OpdVisitInvoiceService
{
    /**
     * Create or return the billing invoice for an OPD visit, with a default consultation charge if new.
     */
    /**
     * @return array{invoice: BillingInvoice, created: bool}
     */
    public function createOrOpenForVisit(OpdVisit $visit, ?int $createdBy = null): array
    {
        $visit->loadMissing('patient');

        $existing = BillingInvoice::query()
            ->where('opd_visit_id', $visit->id)
            ->where('status', '!=', BillingInvoice::STATUS_CANCELLED)
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            return ['invoice' => $existing, 'created' => false];
        }

        $invoice = DB::transaction(function () use ($visit, $createdBy) {
            $invoice = new BillingInvoice;
            $invoice->invoice_number = BillingInvoice::generateNextInvoiceNumber();
            $invoice->patient_id = $visit->patient_id;
            $invoice->opd_visit_id = $visit->id;
            $invoice->status = BillingInvoice::STATUS_DRAFT;
            $invoice->issued_at = now();
            $invoice->notes = trans('admin/opd_visits/table.billing_note', [
                'visit' => $visit->visit_number,
            ]);
            $invoice->company_id = $visit->company_id;
            $invoice->created_by = $createdBy;

            if (! $invoice->save()) {
                throw new \RuntimeException(trans('admin/opd_visits/message.billing.failed'));
            }

            $this->addDefaultConsultationLineForVisitType($invoice, $visit->visit_type);

            $invoice->issue();

            return $invoice->fresh(['lineItems', 'patient', 'opdVisit']);
        });

        return ['invoice' => $invoice, 'created' => true];
    }

    public function addDefaultConsultationLineForVisitType(BillingInvoice $invoice, string $visitType): void
    {
        $code = match ($visitType) {
            OpdVisit::TYPE_FOLLOW_UP => 'FOLLOWUP',
            default => 'CONSULT',
        };

        $service = BillableService::query()->where('code', $code)->where('is_active', true)->first()
            ?? BillableService::query()->where('code', 'CONSULT')->first();

        if (! $service) {
            return;
        }

        $line = new BillingLineItem;
        $line->billing_invoice_id = $invoice->id;
        $line->billable_service_id = $service->id;
        $line->description = $service->name;
        $line->quantity = 1;
        $line->unit_amount = (float) $service->default_amount;
        $line->line_total = BillingLineItem::computeLineTotal(1, $line->unit_amount);
        $line->save();
    }
}
