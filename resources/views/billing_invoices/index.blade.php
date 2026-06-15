@extends('layouts/default')



@section('title')

    {{ trans('admin/billing_invoices/table.title') }}

    @parent

@stop



@section('content')

    <div class="row">

        <div class="col-md-12">

            <div class="box box-default ahop-panel">

                <div class="box-header with-border">

                    <h2 class="box-title">{{ trans('admin/billing_invoices/table.title') }}</h2>

                    <div class="box-tools pull-right">

                        @can('create', \App\Models\BillingInvoice::class)

                            <a href="{{ route('billing-invoices.create') }}" class="btn btn-primary">

                                <i class="fas fa-plus" aria-hidden="true"></i> {{ trans('admin/billing_invoices/table.create') }}

                            </a>

                        @endcan

                    </div>

                </div>



                <div class="box-body">

                    <div class="ahop-billing-stat">

                        <span class="ahop-billing-stat__label">{{ trans('admin/billing_invoices/table.today_collections') }}</span>

                        <span class="ahop-billing-stat__value">₱{{ number_format($todayCollections, 2) }}</span>

                    </div>



                    <form method="get" action="{{ route('billing-invoices.index') }}" class="ahop-billing-filters">

                        <div class="form-group">

                            <input type="text" name="search" class="form-control" placeholder="{{ trans('general.search') }}"

                                   value="{{ request('search') }}">

                        </div>

                        <div class="form-group">

                            <select name="status" class="form-control">

                                <option value="">{{ trans('admin/billing_invoices/table.status') }} — {{ trans('general.all') }}</option>

                                @foreach (\App\Models\BillingInvoice::statusOptions() as $value => $label)

                                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>

                                @endforeach

                            </select>

                        </div>

                        <button type="submit" class="btn btn-default">

                            <i class="fas fa-search" aria-hidden="true"></i> {{ trans('general.search') }}

                        </button>

                    </form>



                    <div class="table-responsive">

                        <table class="table table-bordered ahop-billing-table">

                            <thead>

                            <tr>

                                <th>{{ trans('admin/billing_invoices/table.invoice_number') }}</th>

                                <th>{{ trans('admin/billing_invoices/table.patient') }}</th>

                                <th>{{ trans('admin/billing_invoices/table.status') }}</th>

                                <th class="text-right">{{ trans('admin/billing_invoices/table.subtotal') }}</th>

                                <th class="text-right">{{ trans('admin/billing_invoices/table.balance') }}</th>

                                <th>{{ trans('admin/billing_invoices/table.issued_at') }}</th>

                                <th class="text-right">{{ trans('table.actions') }}</th>

                            </tr>

                            </thead>

                            <tbody>

                            @forelse ($invoices as $invoice)

                                <tr>

                                    <td>

                                        <a href="{{ route('billing-invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>

                                    </td>

                                    <td>

                                        @if ($invoice->patient)

                                            {{ $invoice->patient->full_name }}

                                            <small class="text-muted">({{ $invoice->patient->patient_number }})</small>

                                        @endif

                                    </td>

                                    <td>

                                        <span class="ahop-badge ahop-badge-invoice-{{ $invoice->status }}">

                                            {{ \App\Models\BillingInvoice::statusOptions()[$invoice->status] ?? $invoice->status }}

                                        </span>

                                    </td>

                                    <td class="text-right">₱{{ number_format($invoice->subtotal, 2) }}</td>

                                    <td class="text-right">

                                        @if ($invoice->balance > 0)

                                            <strong>₱{{ number_format($invoice->balance, 2) }}</strong>

                                        @else

                                            ₱{{ number_format($invoice->balance, 2) }}

                                        @endif

                                    </td>

                                    <td>{{ $invoice->issued_at?->format('Y-m-d H:i') ?: '—' }}</td>

                                    <td class="text-right">

                                        @can('view', $invoice)

                                            <a href="{{ route('billing-invoices.show', $invoice) }}" class="btn btn-xs btn-default">{{ trans('general.view') }}</a>

                                        @endcan

                                    </td>

                                </tr>

                            @empty
                                <tr class="ahop-empty-row">
                                    <td colspan="7">
                                        @include('partials.ahop-empty-state', [
                                            'icon' => 'fa-file-invoice-dollar',
                                            'title' => trans('ahop.empty_billing_title'),
                                            'message' => trans('ahop.empty_billing_message'),
                                            'actionUrl' => auth()->user()->can('create', \App\Models\BillingInvoice::class) ? route('billing-invoices.create') : null,
                                            'actionLabel' => auth()->user()->can('create', \App\Models\BillingInvoice::class) ? trans('admin/billing_invoices/table.create') : null,
                                            'compact' => true,
                                        ])
                                    </td>
                                </tr>
                            @endforelse

                            </tbody>

                        </table>

                    </div>



                    {{ $invoices->links() }}

                </div>

            </div>

        </div>

    </div>

@stop

