<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\BillingInvoice;
use App\Models\OpdVisit;
use Illuminate\Support\Facades\DB;

class AppointmentInvoiceService
{
    public function __construct(
        protected OpdVisitInvoiceService $opdVisitInvoiceService
    ) {}

    /**
     * @return array{invoice: BillingInvoice, created: bool}
     */
    public function createOrOpenForAppointment(Appointment $appointment, ?int $createdBy = null): array
    {
        $appointment->loadMissing(['patient', 'opdVisit']);

        if ($appointment->opdVisit) {
            return $this->opdVisitInvoiceService->createOrOpenForVisit($appointment->opdVisit, $createdBy);
        }

        $existing = BillingInvoice::query()
            ->where('appointment_id', $appointment->id)
            ->where('status', '!=', BillingInvoice::STATUS_CANCELLED)
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            return ['invoice' => $existing, 'created' => false];
        }

        $invoice = DB::transaction(function () use ($appointment, $createdBy) {
            $invoice = new BillingInvoice;
            $invoice->invoice_number = BillingInvoice::generateNextInvoiceNumber();
            $invoice->patient_id = $appointment->patient_id;
            $invoice->appointment_id = $appointment->id;
            $invoice->status = BillingInvoice::STATUS_DRAFT;
            $invoice->issued_at = now();
            $invoice->notes = trans('admin/appointments/table.billing_note', [
                'appointment' => $appointment->appointment_number,
            ]);
            $invoice->company_id = $appointment->company_id;
            $invoice->created_by = $createdBy;

            if (! $invoice->save()) {
                throw new \RuntimeException(trans('admin/appointments/message.billing.failed'));
            }

            $this->opdVisitInvoiceService->addDefaultConsultationLineForVisitType(
                $invoice,
                $appointment->visit_type
            );

            $invoice->issue();

            return $invoice->fresh(['lineItems', 'patient', 'appointment']);
        });

        return ['invoice' => $invoice, 'created' => true];
    }
}
