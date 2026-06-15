@extends('layouts/default')

@section('title')
    {{ $invoice->display_name }}
    @parent
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default ahop-panel ahop-clinical-detail-panel">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('admin/billing_invoices/table.invoice_number') }}: {{ $invoice->invoice_number }}</h2>
                    <div class="box-tools pull-right">
                        <a href="{{ route('billing-invoices.receipt', $invoice) }}" class="btn btn-sm btn-default" target="_blank">
                            <i class="fas fa-print" aria-hidden="true"></i> {{ trans('admin/billing_invoices/table.print_receipt') }}
                        </a>
                        @can('update', $invoice)
                            <a href="{{ route('billing-invoices.edit', $invoice) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-pencil" aria-hidden="true"></i> {{ trans('general.edit') }}
                            </a>
                        @endcan
                        @can('delete', $invoice)
                            <form method="post" action="{{ route('billing-invoices.destroy', $invoice) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin/billing_invoices/message.delete.confirm') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash" aria-hidden="true"></i> {{ trans('general.delete') }}
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>

                <div class="box-body">
                    <table class="table">
                        <tbody>
                        <tr>
                            <th style="width: 30%;">{{ trans('admin/billing_invoices/table.patient') }}</th>
                            <td>
                                @if ($invoice->patient)
                                    <a href="{{ route('patients.show', $invoice->patient) }}">
                                        {{ $invoice->patient->full_name }} ({{ $invoice->patient->patient_number }})
                                    </a>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/billing_invoices/table.status') }}</th>
                            <td>{{ \App\Models\BillingInvoice::statusOptions()[$invoice->status] ?? $invoice->status }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/billing_invoices/table.subtotal') }}</th>
                            <td>₱{{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/billing_invoices/table.amount_paid') }}</th>
                            <td>₱{{ number_format($invoice->amount_paid, 2) }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/billing_invoices/table.balance') }}</th>
                            <td><strong>₱{{ number_format($invoice->balance, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/billing_invoices/table.issued_at') }}</th>
                            <td>{{ $invoice->issued_at?->format('Y-m-d H:i') ?: '—' }}</td>
                        </tr>
                        @if ($invoice->notes)
                            <tr>
                                <th>{{ trans('admin/billing_invoices/table.notes') }}</th>
                                <td>{{ $invoice->notes }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>

                    <hr>
                    <h4 class="ahop-section-title">{{ trans('admin/billing_invoices/table.line_items') }}</h4>

                    @if ($invoice->lineItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ trans('admin/billing_invoices/table.description') }}</th>
                                    <th>{{ trans('admin/billing_invoices/table.quantity') }}</th>
                                    <th class="text-right">{{ trans('admin/billing_invoices/table.unit_amount') }}</th>
                                    <th class="text-right">{{ trans('admin/billing_invoices/table.line_total') }}</th>
                                    @can('update', $invoice)
                                        <th></th>
                                    @endcan
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($invoice->lineItems as $line)
                                    <tr>
                                        <td>{{ $line->description }}</td>
                                        <td>{{ $line->quantity }}</td>
                                        <td class="text-right">₱{{ number_format($line->unit_amount, 2) }}</td>
                                        <td class="text-right">₱{{ number_format($line->line_total, 2) }}</td>
                                        @can('update', $invoice)
                                            <td class="text-right">
                                                @if ($invoice->status !== \App\Models\BillingInvoice::STATUS_CANCELLED)
                                                    <form method="post" action="{{ route('billing-invoices.line-items.destroy', [$invoice, $line]) }}" style="display:inline;" onsubmit="return confirm('{{ trans('general.delete') }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-times" aria-hidden="true"></i></button>
                                                    </form>
                                                @endif
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">{{ trans('general.no_results') }}</p>
                    @endif

                    @can('update', $invoice)
                        @if ($invoice->status !== \App\Models\BillingInvoice::STATUS_CANCELLED)
                            <form method="post" action="{{ route('billing-invoices.line-items.store', $invoice) }}" class="ahop-billing-inline-form">
                                @csrf
                                <p class="ahop-billing-inline-form__title">{{ trans('admin/billing_invoices/table.add_line_item') }}</p>
                                <div class="row ahop-billing-inline-form__fields">
                                    <div class="col-sm-12 col-md-5">
                                        <div class="form-group">
                                            <label for="billable_service_id" class="control-label">{{ trans('admin/billing_invoices/table.service') }}</label>
                                            <select name="billable_service_id" id="billable_service_id" class="form-control">
                                                <option value="">{{ trans('general.select') }}...</option>
                                                @foreach ($services as $service)
                                                    <option value="{{ $service->id }}" data-amount="{{ $service->default_amount }}">
                                                        {{ $service->name }} (₱{{ number_format($service->default_amount, 2) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xs-6 col-sm-4 col-md-2">
                                        <div class="form-group">
                                            <label for="line_quantity" class="control-label">{{ trans('admin/billing_invoices/table.quantity') }}</label>
                                            <input type="number" name="quantity" id="line_quantity" class="form-control" value="1" min="1" max="999">
                                        </div>
                                    </div>
                                    <div class="col-xs-6 col-sm-4 col-md-2">
                                        <div class="form-group">
                                            <label for="unit_amount" class="control-label">{{ trans('admin/billing_invoices/table.unit_amount') }}</label>
                                            <input type="number" name="unit_amount" id="unit_amount" class="form-control" step="0.01" min="0">
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-3">
                                        <div class="form-group">
                                            <label for="line_description" class="control-label">{{ trans('admin/billing_invoices/table.description') }}</label>
                                            <input type="text" name="description" id="line_description" class="form-control" placeholder="{{ trans('general.optional') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="ahop-billing-inline-form__footer">
                                    <button type="submit" class="btn btn-primary" title="{{ trans('admin/billing_invoices/table.add_line_item') }}">
                                        <i class="fas fa-plus" aria-hidden="true"></i> {{ trans('general.add') }}
                                    </button>
                                </div>
                            </form>
                        @endif
                    @endcan

                    <hr>
                    <h4 class="ahop-section-title">{{ trans('admin/billing_invoices/table.payments') }}</h4>

                    @if ($invoice->payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ trans('admin/billing_invoices/table.paid_at') }}</th>
                                    <th>{{ trans('admin/billing_invoices/table.payment_method') }}</th>
                                    <th class="text-right">{{ trans('admin/billing_invoices/table.payment_amount') }}</th>
                                    <th>{{ trans('admin/billing_invoices/table.payment_reference') }}</th>
                                    @can('update', $invoice)
                                        <th></th>
                                    @endcan
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($invoice->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->paid_at?->format('Y-m-d H:i') }}</td>
                                        <td>{{ \App\Models\BillingPayment::paymentMethodOptions()[$payment->payment_method] ?? $payment->payment_method }}</td>
                                        <td class="text-right">₱{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->reference ?: '—' }}</td>
                                        @can('update', $invoice)
                                            <td class="text-right">
                                                @if ($invoice->status !== \App\Models\BillingInvoice::STATUS_CANCELLED)
                                                    <form method="post" action="{{ route('billing-invoices.payments.destroy', [$invoice, $payment]) }}" style="display:inline;" onsubmit="return confirm('{{ trans('general.delete') }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-times" aria-hidden="true"></i></button>
                                                    </form>
                                                @endif
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif ($invoice->balance <= 0 || $invoice->status === \App\Models\BillingInvoice::STATUS_CANCELLED)
                        <p class="text-muted">{{ trans('admin/billing_invoices/table.no_payments') }}</p>
                    @endif

                    @can('update', $invoice)
                        @if ($invoice->status !== \App\Models\BillingInvoice::STATUS_CANCELLED && $invoice->balance > 0)
                            <form method="post" action="{{ route('billing-invoices.payments.store', $invoice) }}" class="ahop-billing-inline-form ahop-billing-payment-form">
                                @csrf
                                <p class="ahop-billing-inline-form__title">{{ trans('admin/billing_invoices/table.add_payment') }}</p>
                                <p class="ahop-billing-inline-form__hint text-muted">
                                    {{ trans('admin/billing_invoices/table.balance_due') }}:
                                    <strong>₱{{ number_format($invoice->balance, 2) }}</strong>
                                </p>
                                <div class="row ahop-billing-inline-form__fields">
                                    <div class="col-sm-6 col-md-3">
                                        <div class="form-group">
                                            <label for="payment_amount" class="control-label">{{ trans('admin/billing_invoices/table.payment_amount') }}</label>
                                            <input type="number" name="amount" id="payment_amount" class="form-control" step="0.01" min="0.01" max="{{ $invoice->balance }}" value="{{ $invoice->balance }}" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <div class="form-group">
                                            <label for="payment_method" class="control-label">{{ trans('admin/billing_invoices/table.payment_method') }}</label>
                                            <select name="payment_method" id="payment_method" class="form-control" required>
                                                @foreach (\App\Models\BillingPayment::paymentMethodOptions() as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <div class="form-group">
                                            <label for="payment_reference" class="control-label">{{ trans('admin/billing_invoices/table.payment_reference') }}</label>
                                            <input type="text" name="reference" id="payment_reference" class="form-control" placeholder="{{ trans('general.optional') }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <div class="form-group">
                                            <label for="payment_paid_at" class="control-label">{{ trans('admin/billing_invoices/table.paid_at') }}</label>
                                            <input type="datetime-local" name="paid_at" id="payment_paid_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="ahop-billing-inline-form__footer">
                                    <button type="submit" class="btn btn-success" title="{{ trans('admin/billing_invoices/table.add_payment') }}">
                                        <i class="fas fa-money-bill-wave" aria-hidden="true"></i> {{ trans('admin/billing_invoices/table.record_payment') }}
                                    </button>
                                </div>
                            </form>
                        @endif
                    @endcan
                </div>

                <div class="box-footer text-right">
                    <a href="{{ route('billing-invoices.index') }}" class="btn btn-default">{{ trans('general.back') }}</a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
    document.getElementById('billable_service_id')?.addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        var amount = opt.getAttribute('data-amount');
        if (amount !== null && amount !== '') {
            document.getElementById('unit_amount').value = amount;
        }
    });
</script>
@stop
