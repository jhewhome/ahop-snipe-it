<?php

namespace App\Services\Ahop;

use App\Models\Appointment;
use App\Models\BillingInvoice;
use App\Models\LabOrder;
use App\Models\OpdVisit;
use App\Models\Patient;
use Illuminate\Support\Collection;

class PatientTimelineBuilder
{
    /**
     * @return list<array{at: \Carbon\Carbon|null, type: string, label: string, detail: string, url: string|null}>
     */
    public function build(Patient $patient, int $limit = 40): array
    {
        $events = collect();

        foreach ($patient->opdVisits as $visit) {
            $events->push([
                'at' => $visit->visit_date,
                'type' => 'opd',
                'label' => trans('admin/clinical_reports/timeline.opd_visit'),
                'detail' => $visit->visit_number.' — '.(OpdVisit::statusOptions()[$visit->status] ?? $visit->status),
                'url' => route('opd-visits.show', $visit),
            ]);
        }

        foreach ($patient->appointments as $appointment) {
            $events->push([
                'at' => $appointment->scheduled_at,
                'type' => 'appointment',
                'label' => trans('admin/clinical_reports/timeline.appointment'),
                'detail' => $appointment->appointment_number.' — '.(Appointment::statusOptions()[$appointment->status] ?? $appointment->status),
                'url' => route('appointments.show', $appointment),
            ]);
        }

        foreach ($patient->labOrders as $order) {
            $events->push([
                'at' => $order->ordered_at,
                'type' => 'lab',
                'label' => trans('admin/clinical_reports/timeline.lab_order'),
                'detail' => $order->order_number.' — '.(LabOrder::statusOptions()[$order->status] ?? $order->status),
                'url' => route('lab-orders.show', $order),
            ]);
        }

        foreach ($patient->billingInvoices as $invoice) {
            $events->push([
                'at' => $invoice->issued_at ?? $invoice->created_at,
                'type' => 'billing',
                'label' => trans('admin/clinical_reports/timeline.invoice'),
                'detail' => $invoice->invoice_number.' — ₱'.number_format($invoice->balance, 2).' '.trans('admin/billing_invoices/table.balance'),
                'url' => route('billing-invoices.show', $invoice),
            ]);
        }

        return $events
            ->filter(fn (array $event) => $event['at'] !== null)
            ->sortByDesc('at')
            ->take($limit)
            ->values()
            ->all();
    }
}
