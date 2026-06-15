<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $patient->patient_number }} — {{ trans('admin/patients/table.summary_title') }}</title>
    <style>
        :root { --ahop-primary: #0d6e7a; --ahop-border: #d8e2e8; }
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", Arial, sans-serif; font-size: 14px; margin: 0; padding: 24px; color: #1a2b33; background: #f4f8fa; }
        .summary { max-width: 820px; margin: 0 auto; background: #fff; border: 1px solid var(--ahop-border); border-radius: 8px; padding: 28px 32px; }
        .summary-header { border-bottom: 3px solid var(--ahop-primary); padding-bottom: 16px; margin-bottom: 20px; }
        .summary-header .clinic-name { margin: 0 0 4px; color: var(--ahop-primary); font-size: 22px; font-weight: 600; }
        .summary-header h1 { margin: 0 0 6px; color: #094a52; font-size: 18px; font-weight: 600; }
        .summary-header p { margin: 0; color: #5c6b73; font-size: 13px; }
        h2 { font-size: 16px; color: var(--ahop-primary); margin: 24px 0 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid var(--ahop-border); padding: 8px 10px; text-align: left; vertical-align: top; }
        th { background: #eef6f8; width: 30%; }
        .alert-box { background: #fff3e0; border: 1px solid #ffcc80; border-radius: 6px; padding: 12px 14px; margin-bottom: 16px; }
        .alert-box strong { color: #e65100; }
        .muted { color: #64748b; }
        .toolbar { max-width: 820px; margin: 0 auto 16px; text-align: right; }
        .toolbar button, .toolbar a { display: inline-block; margin-left: 8px; padding: 8px 14px; border-radius: 4px; text-decoration: none; border: 1px solid #cbd5e1; background: #fff; color: #334155; cursor: pointer; }
        .toolbar button.primary { background: var(--ahop-primary); color: #fff; border-color: var(--ahop-primary); }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .summary { border: none; border-radius: 0; padding: 0; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <a href="{{ route('patients.show', $patient) }}">{{ trans('general.back') }}</a>
    <button type="button" class="primary" onclick="window.print();">{{ trans('admin/patients/table.summary_print') }}</button>
</div>

<div class="summary">
    <div class="summary-header">
        <p class="clinic-name">{{ ($snipeSettings->site_name ?? '') ?: config('ahop.default_site_name', 'AgilityCare Health Operations Platform') }}</p>
        <h1>{{ trans('admin/patients/table.summary_title') }}</h1>
        <p>{{ $patient->full_name }} ({{ $patient->patient_number }}) — {{ trans('admin/patients/table.summary_generated') }} {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <h2>{{ trans('admin/patients/table.summary_demographics') }}</h2>
    <table>
        <tr><th>{{ trans('admin/patients/table.full_name') }}</th><td>{{ $patient->full_name }}</td></tr>
        <tr><th>{{ trans('admin/patients/table.sex') }}</th><td>{{ $patient->sex === 'M' ? 'Male' : 'Female' }}</td></tr>
        <tr><th>{{ trans('admin/patients/table.birthdate') }}</th><td>{{ $patient->birthdate?->format('Y-m-d') ?? '—' }}</td></tr>
        <tr><th>{{ trans('admin/patients/table.contact_number') }}</th><td>{{ $patient->contact_number ?: '—' }}</td></tr>
        <tr><th>{{ trans('admin/patients/table.email') }}</th><td>{{ $patient->email ?: '—' }}</td></tr>
    </table>

    @if ($patient->allergies || $patient->problem_list)
        <div class="alert-box">
            @if ($patient->allergies)
                <p style="margin: 0 0 8px;"><strong>{{ trans('admin/patients/table.allergies') }}:</strong> {{ $patient->allergies }}</p>
            @endif
            @if ($patient->problem_list)
                <p style="margin: 0;"><strong>{{ trans('admin/patients/table.problem_list') }}:</strong> {{ $patient->problem_list }}</p>
            @endif
        </div>
    @endif

    <h2>{{ trans('admin/patients/table.recent_opd') }}</h2>
    @if ($patient->opdVisits->count())
        <table>
            <thead>
            <tr>
                <th>{{ trans('admin/opd_visits/table.visit_number') }}</th>
                <th>{{ trans('admin/opd_visits/table.visit_date') }}</th>
                <th>{{ trans('admin/opd_visits/table.physician') }}</th>
                <th>{{ trans('admin/opd_visits/table.status') }}</th>
                <th>{{ trans('admin/opd_visits/table.diagnosis') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($patient->opdVisits as $visit)
                <tr>
                    <td>{{ $visit->visit_number }}</td>
                    <td>{{ $visit->visit_date?->format('Y-m-d H:i') }}</td>
                    <td>{{ $visit->physician?->present()->fullName ?? '—' }}</td>
                    <td>{{ \App\Models\OpdVisit::statusOptions()[$visit->status] ?? $visit->status }}</td>
                    <td>{{ $visit->diagnosis ?: '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="muted">{{ trans('admin/patients/table.no_recent_opd') }}</p>
    @endif

    <h2>{{ trans('admin/patients/table.recent_labs') }}</h2>
    @if ($patient->labOrders->count())
        <table>
            <thead>
            <tr>
                <th>{{ trans('admin/lab_orders/table.order_number') }}</th>
                <th>{{ trans('admin/lab_orders/table.test_panel') }}</th>
                <th>{{ trans('admin/lab_orders/table.status') }}</th>
                <th>{{ trans('admin/lab_orders/table.ordered_at') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($patient->labOrders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ \App\Models\LabOrder::testPanelOptions()[$order->test_panel] ?? $order->test_panel }}</td>
                    <td>{{ \App\Models\LabOrder::statusOptions()[$order->status] ?? $order->status }}</td>
                    <td>{{ $order->ordered_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="muted">{{ trans('admin/patients/table.no_recent_labs') }}</p>
    @endif

    <h2>{{ trans('admin/patients/table.recent_billing') }}</h2>
    @if ($patient->billingInvoices->count())
        <table>
            <thead>
            <tr>
                <th>{{ trans('admin/billing_invoices/table.invoice_number') }}</th>
                <th>{{ trans('admin/billing_invoices/table.status') }}</th>
                <th>{{ trans('admin/billing_invoices/table.balance') }}</th>
                <th>{{ trans('admin/billing_invoices/table.issued_at') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($patient->billingInvoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_number }}</td>
                    <td>{{ \App\Models\BillingInvoice::statusOptions()[$invoice->status] ?? $invoice->status }}</td>
                    <td>₱{{ number_format($invoice->balance, 2) }}</td>
                    <td>{{ $invoice->issued_at?->format('Y-m-d') ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="muted">{{ trans('admin/patients/table.no_recent_billing') }}</p>
    @endif
</div>
</body>
</html>
