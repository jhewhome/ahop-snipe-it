<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <title>{{ $invoice->invoice_number }} — {{ trans('admin/billing_invoices/table.print_receipt') }}</title>

    <style>

        :root {

            --ahop-primary: #0d6e7a;

            --ahop-primary-dark: #084a52;

            --ahop-muted: #5c6b73;

            --ahop-border: #d8e2e8;

        }

        * { box-sizing: border-box; }

        body {

            font-family: "Segoe UI", Arial, sans-serif;

            font-size: 14px;

            margin: 0;

            padding: 24px;

            color: #1a2b33;

            background: #f4f8fa;

        }

        .receipt {

            max-width: 720px;

            margin: 0 auto;

            background: #fff;

            border: 1px solid var(--ahop-border);

            border-radius: 8px;

            padding: 28px 32px;

        }

        .receipt-header {

            border-bottom: 3px solid var(--ahop-primary);

            padding-bottom: 16px;

            margin-bottom: 20px;

        }

        .receipt-header h1 {

            font-size: 22px;

            margin: 0 0 4px;

            color: var(--ahop-primary-dark);

        }

        .receipt-header .subtitle {

            color: var(--ahop-muted);

            font-size: 13px;

            margin: 0;

        }

        .meta {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 8px 24px;

            margin-bottom: 20px;

            font-size: 13px;

        }

        .meta strong { color: var(--ahop-primary-dark); }

        table.data {

            width: 100%;

            border-collapse: collapse;

            margin-top: 8px;

        }

        table.data th,

        table.data td {

            border-bottom: 1px solid var(--ahop-border);

            padding: 10px 6px;

            text-align: left;

        }

        table.data th {

            background: #eef6f8;

            color: var(--ahop-primary-dark);

            font-size: 12px;

            text-transform: uppercase;

            letter-spacing: 0.03em;

        }

        .text-right { text-align: right; }

        .totals {

            margin-top: 20px;

            width: 300px;

            margin-left: auto;

        }

        .totals td {

            border: none;

            padding: 6px 4px;

        }

        .totals tr:last-child td {

            border-top: 2px solid var(--ahop-primary);

            padding-top: 10px;

            font-size: 16px;

        }

        .section-title {

            font-size: 15px;

            margin: 28px 0 8px;

            color: var(--ahop-primary-dark);

        }

        .footer-note {

            margin-top: 32px;

            padding-top: 16px;

            border-top: 1px dashed var(--ahop-border);

            color: var(--ahop-muted);

            font-size: 12px;

        }

        .no-print {

            max-width: 720px;

            margin: 0 auto 16px;

            text-align: right;

        }

        .no-print button {

            background: var(--ahop-primary);

            color: #fff;

            border: none;

            border-radius: 6px;

            padding: 10px 18px;

            font-size: 14px;

            cursor: pointer;

        }

        .no-print button:hover { background: var(--ahop-primary-dark); }

        @media print {

            body { background: #fff; padding: 0; }

            .no-print { display: none; }

            .receipt { border: none; border-radius: 0; padding: 0; max-width: none; }

        }

    </style>

</head>

<body>

    <div class="no-print">

        <button type="button" onclick="window.print()">{{ trans('admin/billing_invoices/table.print_receipt') }}</button>

    </div>



    <div class="receipt">

        <header class="receipt-header">

            <h1>{{ config('ahop.default_site_name', 'AgilityCare') }}</h1>

            <p class="subtitle">{{ trans('admin/billing_invoices/table.print_receipt') }} — {{ $invoice->invoice_number }}</p>

        </header>



        <div class="meta">

            <div>

                <strong>{{ trans('admin/billing_invoices/table.patient') }}</strong><br>

                {{ $invoice->patient?->full_name }}<br>

                <span style="color: var(--ahop-muted);">{{ $invoice->patient?->patient_number }}</span>

            </div>

            <div>

                <strong>{{ trans('admin/billing_invoices/table.issued_at') }}</strong><br>

                {{ $invoice->issued_at?->format('Y-m-d H:i') ?: '—' }}<br>

                <strong style="margin-top: 8px; display: inline-block;">{{ trans('admin/billing_invoices/table.status') }}</strong><br>

                {{ \App\Models\BillingInvoice::statusOptions()[$invoice->status] ?? $invoice->status }}

            </div>

        </div>



        <table class="data">

            <thead>

            <tr>

                <th>{{ trans('admin/billing_invoices/table.description') }}</th>

                <th>{{ trans('admin/billing_invoices/table.quantity') }}</th>

                <th class="text-right">{{ trans('admin/billing_invoices/table.line_total') }}</th>

            </tr>

            </thead>

            <tbody>

            @foreach ($invoice->lineItems as $line)

                <tr>

                    <td>{{ $line->description }}</td>

                    <td>{{ $line->quantity }}</td>

                    <td class="text-right">₱{{ number_format($line->line_total, 2) }}</td>

                </tr>

            @endforeach

            </tbody>

        </table>



        <table class="data totals">

            <tr>

                <td>{{ trans('admin/billing_invoices/table.subtotal') }}</td>

                <td class="text-right">₱{{ number_format($invoice->subtotal, 2) }}</td>

            </tr>

            <tr>

                <td>{{ trans('admin/billing_invoices/table.amount_paid') }}</td>

                <td class="text-right">₱{{ number_format($invoice->amount_paid, 2) }}</td>

            </tr>

            <tr>

                <td><strong>{{ trans('admin/billing_invoices/table.balance') }}</strong></td>

                <td class="text-right"><strong>₱{{ number_format($invoice->balance, 2) }}</strong></td>

            </tr>

        </table>



        @if ($invoice->payments->count() > 0)

            <h2 class="section-title">{{ trans('admin/billing_invoices/table.payments') }}</h2>

            <table class="data">

                <thead>

                <tr>

                    <th>{{ trans('admin/billing_invoices/table.paid_at') }}</th>

                    <th>{{ trans('admin/billing_invoices/table.payment_method') }}</th>

                    <th class="text-right">{{ trans('admin/billing_invoices/table.payment_amount') }}</th>

                </tr>

                </thead>

                <tbody>

                @foreach ($invoice->payments as $payment)

                    <tr>

                        <td>{{ $payment->paid_at?->format('Y-m-d H:i') }}</td>

                        <td>{{ \App\Models\BillingPayment::paymentMethodOptions()[$payment->payment_method] ?? $payment->payment_method }}</td>

                        <td class="text-right">₱{{ number_format($payment->amount, 2) }}</td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        @endif



        <p class="footer-note">

            {{ trans('admin/billing_invoices/table.receipt_footer', ['printed' => now()->format('Y-m-d H:i')]) }}

        </p>

    </div>

</body>

</html>

