<?php

namespace App\Services\Ahop;

use App\Models\Appointment;
use App\Models\BillingInvoice;
use App\Models\BillingLineItem;
use App\Models\BillingPayment;
use App\Models\LabOrder;
use App\Models\OpdVisit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ClinicalReportService
{
    public const REPORT_TYPES = [
        'daily_summary',
        'collections',
        'opd_visits',
        'lab_turnaround',
        'revenue_by_service',
        'physician_visits',
        'invoice_aging',
    ];

    /**
     * @return array{headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    public function export(string $type, Carbon $from, Carbon $to): array
    {
        return match ($type) {
            'daily_summary' => $this->dailySummary($from, $to),
            'collections' => $this->collections($from, $to),
            'opd_visits' => $this->opdVisits($from, $to),
            'lab_turnaround' => $this->labTurnaround($from, $to),
            'revenue_by_service' => $this->revenueByService($from, $to),
            'physician_visits' => $this->physicianVisits($from, $to),
            'invoice_aging' => $this->invoiceAging(),
            default => throw new \InvalidArgumentException('Unknown report type: '.$type),
        };
    }

    /**
     * Chart.js payload for the clinical reports dashboard (Phase C).
     *
     * @return array<string, array{labels: list<string>, values: list<int|float>, colors?: list<string>}>
     */
    public function chartDashboard(Carbon $from, Carbon $to): array
    {
        return [
            'opd_visits' => $this->chartOpdVisitsByPeriod($from, $to),
            'collections' => $this->chartCollectionsByPeriod($from, $to),
            'revenue_by_service' => $this->chartRevenueByService($from, $to),
            'invoice_aging' => $this->chartInvoiceAgingBuckets(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    protected function chartOpdVisitsByPeriod(Carbon $from, Carbon $to): array
    {
        $counts = OpdVisit::query()
            ->whereBetween('visit_date', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->get()
            ->filter(fn (OpdVisit $visit) => $visit->visit_date !== null)
            ->groupBy(fn (OpdVisit $visit) => $visit->visit_date->toDateString())
            ->map->count();

        return $this->fillTimeSeries($from, $to, $counts, 'int');
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    protected function chartCollectionsByPeriod(Carbon $from, Carbon $to): array
    {
        $totals = BillingPayment::query()
            ->whereBetween('paid_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->get()
            ->filter(fn (BillingPayment $payment) => $payment->paid_at !== null)
            ->groupBy(fn (BillingPayment $payment) => $payment->paid_at->toDateString())
            ->map(fn (Collection $group) => (float) $group->sum('amount'));

        return $this->fillTimeSeries($from, $to, $totals, 'float');
    }

    /**
     * @return array{labels: list<string>, values: list<float>, colors: list<string>}
     */
    protected function chartRevenueByService(Carbon $from, Carbon $to): array
    {
        $export = $this->revenueByService($from, $to);
        $palette = ['#0d6e7a', '#1496a6', '#2eb8a6', '#059669', '#1565c0', '#6a1b9a', '#e65100', '#64748b'];

        $rows = collect($export['rows'])->take(8)->values();
        if ($rows->isEmpty()) {
            return [
                'labels' => [trans('admin/clinical_reports/general.chart_no_data')],
                'values' => [0],
                'colors' => ['#cbd5e1'],
            ];
        }

        return [
            'labels' => $rows->map(fn (array $row) => (string) $row[1])->all(),
            'values' => $rows->map(fn (array $row) => round((float) $row[4], 2))->all(),
            'colors' => $rows->map(fn ($row, int $index) => $palette[$index % count($palette)])->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>, colors: list<string>}
     */
    protected function chartInvoiceAgingBuckets(): array
    {
        $buckets = [
            '0_30' => 0.0,
            '31_60' => 0.0,
            '61_90' => 0.0,
            '90_plus' => 0.0,
        ];

        foreach ($this->invoiceAging()['rows'] as $row) {
            $days = (int) ($row[5] ?? 0);
            $balance = (float) ($row[8] ?? 0);

            if ($days <= 30) {
                $buckets['0_30'] += $balance;
            } elseif ($days <= 60) {
                $buckets['31_60'] += $balance;
            } elseif ($days <= 90) {
                $buckets['61_90'] += $balance;
            } else {
                $buckets['90_plus'] += $balance;
            }
        }

        return [
            'labels' => [
                trans('admin/clinical_reports/general.chart_aging_0_30'),
                trans('admin/clinical_reports/general.chart_aging_31_60'),
                trans('admin/clinical_reports/general.chart_aging_61_90'),
                trans('admin/clinical_reports/general.chart_aging_90_plus'),
            ],
            'values' => array_map(fn (float $value) => round($value, 2), array_values($buckets)),
            'colors' => ['#2e7d32', '#f9a825', '#e65100', '#c62828'],
        ];
    }

    /**
     * @param  Collection<string, int|float>  $dataByDay
     * @return array{labels: list<string>, values: list<int|float>}
     */
    protected function fillTimeSeries(Carbon $from, Carbon $to, Collection $dataByDay, string $valueType): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->startOfDay();
        $labels = [];
        $values = [];

        if ($from->diffInDays($to) <= 44) {
            for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
                $key = $day->toDateString();
                $labels[] = $day->format('M j');
                $values[] = $this->castSeriesValue($dataByDay[$key] ?? 0, $valueType);
            }

            return compact('labels', 'values');
        }

        for ($weekStart = $from->copy()->startOfWeek(); $weekStart->lte($to); $weekStart->addWeek()) {
            $weekEnd = $weekStart->copy()->endOfWeek();
            if ($weekEnd->gt($to)) {
                $weekEnd = $to->copy();
            }

            $sum = 0;
            for ($day = $weekStart->copy(); $day->lte($weekEnd); $day->addDay()) {
                $sum += $dataByDay[$day->toDateString()] ?? 0;
            }

            $labels[] = $weekStart->format('M j');
            $values[] = $this->castSeriesValue($sum, $valueType);
        }

        return compact('labels', 'values');
    }

    protected function castSeriesValue(int|float $value, string $valueType): int|float
    {
        if ($valueType === 'int') {
            return (int) $value;
        }

        return round((float) $value, 2);
    }

    /**
     * @return array{headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    protected function dailySummary(Carbon $from, Carbon $to): array
    {
        $headers = [
            'period_from',
            'period_to',
            'new_patients',
            'opd_visits',
            'opd_completed',
            'appointments_scheduled',
            'appointments_checked_in',
            'lab_orders',
            'lab_completed',
            'invoices_issued',
            'total_collections',
        ];

        $rows = [[
            $from->toDateString(),
            $to->toDateString(),
            \App\Models\Patient::query()->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->count(),
            OpdVisit::query()->whereBetween('visit_date', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->count(),
            OpdVisit::query()->where('status', OpdVisit::STATUS_COMPLETED)->whereBetween('visit_date', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->count(),
            Appointment::query()->whereBetween('scheduled_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->count(),
            Appointment::query()->where('status', Appointment::STATUS_CHECKED_IN)->whereBetween('scheduled_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->count(),
            LabOrder::query()->whereBetween('ordered_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->count(),
            LabOrder::query()->where('status', LabOrder::STATUS_COMPLETED)->whereBetween('completed_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->count(),
            BillingInvoice::query()->where('status', '!=', BillingInvoice::STATUS_CANCELLED)->whereBetween('issued_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->count(),
            (float) BillingPayment::query()->whereBetween('paid_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->sum('amount'),
        ]];

        return compact('headers', 'rows');
    }

    /**
     * @return array{headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    protected function collections(Carbon $from, Carbon $to): array
    {
        $headers = ['paid_at', 'invoice_number', 'patient_number', 'patient_name', 'amount', 'payment_method', 'reference', 'received_by'];

        $payments = BillingPayment::query()
            ->with(['invoice.patient'])
            ->whereBetween('paid_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderBy('paid_at')
            ->get();

        $userNames = $this->userNameMap($payments->pluck('received_by')->filter());

        $rows = $payments->map(function (BillingPayment $payment) use ($userNames) {
            $invoice = $payment->invoice;
            $patient = $invoice?->patient;

            return [
                $payment->paid_at?->format('Y-m-d H:i'),
                $invoice?->invoice_number,
                $patient?->patient_number,
                $patient?->full_name,
                (float) $payment->amount,
                $payment->payment_method,
                $payment->reference,
                $userNames[$payment->received_by] ?? $payment->received_by,
            ];
        })->all();

        return compact('headers', 'rows');
    }

    /**
     * @return array{headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    protected function opdVisits(Carbon $from, Carbon $to): array
    {
        $headers = ['visit_number', 'visit_date', 'status', 'visit_type', 'patient_number', 'patient_name', 'physician', 'chief_complaint', 'diagnosis'];

        $visits = OpdVisit::query()
            ->with(['patient', 'physician'])
            ->whereBetween('visit_date', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderBy('visit_date')
            ->get();

        $rows = $visits->map(fn (OpdVisit $visit) => [
            $visit->visit_number,
            $visit->visit_date?->format('Y-m-d H:i'),
            $visit->status,
            $visit->visit_type,
            $visit->patient?->patient_number,
            $visit->patient?->full_name,
            $visit->physician?->present()->fullName(),
            $visit->chief_complaint,
            $visit->diagnosis,
        ])->all();

        return compact('headers', 'rows');
    }

    /**
     * @return array{headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    protected function labTurnaround(Carbon $from, Carbon $to): array
    {
        $headers = ['order_number', 'patient_number', 'patient_name', 'test_panel', 'status', 'ordered_at', 'completed_at', 'turnaround_hours', 'result_count'];

        $orders = LabOrder::query()
            ->with(['patient'])
            ->withCount('results')
            ->whereBetween('ordered_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderBy('ordered_at')
            ->get();

        $rows = $orders->map(function (LabOrder $order) {
            $hours = null;
            if ($order->ordered_at && $order->completed_at) {
                $hours = round($order->ordered_at->diffInMinutes($order->completed_at) / 60, 1);
            }

            return [
                $order->order_number,
                $order->patient?->patient_number,
                $order->patient?->full_name,
                $order->test_panel,
                $order->status,
                $order->ordered_at?->format('Y-m-d H:i'),
                $order->completed_at?->format('Y-m-d H:i'),
                $hours,
                $order->results_count,
            ];
        })->all();

        return compact('headers', 'rows');
    }

    /**
     * @return array{headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    protected function revenueByService(Carbon $from, Carbon $to): array
    {
        $headers = ['service_code', 'service_name', 'line_count', 'total_quantity', 'total_amount'];

        $lines = BillingLineItem::query()
            ->with('billableService')
            ->whereHas('invoice', function ($q) use ($from, $to) {
                $q->where('status', '!=', BillingInvoice::STATUS_CANCELLED)
                    ->whereBetween('issued_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
            })
            ->get();

        $grouped = $lines->groupBy(fn (BillingLineItem $line) => $line->billableService?->code ?? 'CUSTOM');

        $rows = $grouped->map(function (Collection $items, string $code) {
            $first = $items->first();

            return [
                $code,
                $first->billableService?->name ?? $first->description,
                $items->count(),
                $items->sum('quantity'),
                round($items->sum('line_total'), 2),
            ];
        })->values()->sortByDesc(fn ($row) => $row[4])->values()->all();

        return compact('headers', 'rows');
    }

    /**
     * @return array{headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    protected function physicianVisits(Carbon $from, Carbon $to): array
    {
        $headers = ['physician', 'visit_count', 'completed_count', 'in_progress_count'];

        $visits = OpdVisit::query()
            ->with('physician')
            ->whereBetween('visit_date', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->whereNotNull('physician_id')
            ->get()
            ->groupBy('physician_id');

        $rows = $visits->map(function (Collection $group) {
            $physician = $group->first()->physician;

            return [
                $physician?->present()->fullName() ?? 'Unknown',
                $group->count(),
                $group->where('status', OpdVisit::STATUS_COMPLETED)->count(),
                $group->where('status', OpdVisit::STATUS_IN_PROGRESS)->count(),
            ];
        })->values()->sortByDesc(fn ($row) => $row[1])->values()->all();

        return compact('headers', 'rows');
    }

    /**
     * @return array{headers: list<string>, rows: list<list<string|int|float|null>>}
     */
    protected function invoiceAging(): array
    {
        $headers = ['invoice_number', 'patient_number', 'patient_name', 'status', 'issued_at', 'days_outstanding', 'subtotal', 'amount_paid', 'balance'];

        $invoices = BillingInvoice::query()
            ->with('patient')
            ->whereIn('status', [BillingInvoice::STATUS_ISSUED, BillingInvoice::STATUS_PARTIAL, BillingInvoice::STATUS_DRAFT])
            ->where('balance', '>', 0)
            ->orderByDesc('issued_at')
            ->get();

        $rows = $invoices->map(function (BillingInvoice $invoice) {
            $issued = $invoice->issued_at ?? $invoice->created_at;
            $days = $issued ? $issued->diffInDays(now()) : null;

            return [
                $invoice->invoice_number,
                $invoice->patient?->patient_number,
                $invoice->patient?->full_name,
                $invoice->status,
                $issued?->format('Y-m-d'),
                $days,
                (float) $invoice->subtotal,
                (float) $invoice->amount_paid,
                (float) $invoice->balance,
            ];
        })->all();

        return compact('headers', 'rows');
    }

    /**
     * @return array<int, string>
     */
    protected function userNameMap(Collection $ids): array
    {
        if ($ids->isEmpty()) {
            return [];
        }

        return User::query()
            ->whereIn('id', $ids->unique()->all())
            ->get()
            ->mapWithKeys(fn (User $user) => [$user->id => $user->present()->fullName()])
            ->all();
    }
}
