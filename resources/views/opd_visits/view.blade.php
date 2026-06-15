@extends('layouts/default')

@section('title')
    {{ $visit->display_name }}
    @parent
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default ahop-panel ahop-clinical-detail-panel">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('admin/opd_visits/table.visit_number') }}: {{ $visit->visit_number }}</h2>
                    <div class="box-tools pull-right">
                        @can('view', $visit)
                            <a href="{{ route('opd-visits.medical-certificate', $visit) }}" class="btn btn-sm btn-default" target="_blank">
                                <i class="fas fa-file-medical-alt" aria-hidden="true"></i> {{ trans('admin/opd_visits/med_cert.print_med_cert') }}
                            </a>
                        @endcan
                        @can('create', \App\Models\BillingInvoice::class)
                            @if ($visit->activeBillingInvoice)
                                <a href="{{ route('billing-invoices.show', $visit->activeBillingInvoice) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-file-invoice-dollar" aria-hidden="true"></i> {{ trans('admin/opd_visits/table.view_invoice') }}
                                </a>
                            @else
                                <form method="post" action="{{ route('opd-visits.billing-invoice', $visit) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin/opd_visits/message.billing.confirm') }}');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-file-invoice-dollar" aria-hidden="true"></i> {{ trans('admin/opd_visits/table.create_invoice') }}
                                    </button>
                                </form>
                            @endif
                        @endcan
                        @can('create', \App\Models\LabOrder::class)
                            <a href="{{ route('lab-orders.create', ['patient_id' => $visit->patient_id, 'opd_visit_id' => $visit->id]) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-flask" aria-hidden="true"></i> {{ trans('admin/opd_visits/table.order_lab') }}
                            </a>
                        @endcan
                        @can('update', $visit)
                            <a href="{{ route('opd-visits.edit', $visit) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-pencil" aria-hidden="true"></i> {{ trans('general.edit') }}
                            </a>
                        @endcan
                        @can('delete', $visit)
                            <form method="post" action="{{ route('opd-visits.destroy', $visit) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin/opd_visits/message.delete.confirm') }}');">
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
                    @include('partials.ahop-opd-patient-clinical-readonly', ['patient' => $visit->patient])

                    @can('create', \App\Models\LabOrder::class)
                        <div style="margin-bottom: 16px;">
                            <strong>{{ trans('admin/opd_visits/table.quick_order_lab') }}:</strong>
                            @foreach (['CBC', 'BMP', 'LIPID'] as $panel)
                                <form method="post" action="{{ route('opd-visits.lab-orders.store', $visit) }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="test_panel" value="{{ $panel }}">
                                    <button type="submit" class="btn btn-xs btn-default" style="margin-left: 4px;">
                                        {{ \App\Models\LabOrder::testPanelOptions()[$panel] ?? $panel }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    @endcan

                    <table class="table">
                        <tbody>
                        <tr>
                            <th style="width: 30%;">{{ trans('admin/opd_visits/table.patient') }}</th>
                            <td>
                                @if ($visit->patient)
                                    <a href="{{ route('patients.show', $visit->patient) }}">
                                        {{ $visit->patient->full_name }} ({{ $visit->patient->patient_number }})
                                    </a>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/opd_visits/table.visit_date') }}</th>
                            <td>{{ $visit->visit_date?->format('Y-m-d H:i') }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/opd_visits/table.visit_type') }}</th>
                            <td>{{ \App\Models\OpdVisit::visitTypeOptions()[$visit->visit_type] ?? $visit->visit_type }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/opd_visits/table.status') }}</th>
                            <td>{{ \App\Models\OpdVisit::statusOptions()[$visit->status] ?? $visit->status }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/opd_visits/table.physician') }}</th>
                            <td>
                                @if ($visit->physician)
                                    {{ $visit->physician->present()->fullName }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @if ($visit->company_id && ($clinic = $visit->company ?? \App\Models\Company::find($visit->company_id)))
                            <tr>
                                <th>{{ trans('admin/opd_visits/table.clinic_site') }}</th>
                                <td>{{ $clinic->name }}</td>
                            </tr>
                        @endif
                        @if ($visit->chief_complaint)
                            <tr>
                                <th>{{ trans('admin/opd_visits/table.chief_complaint') }}</th>
                                <td>{{ $visit->chief_complaint }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>{{ trans('admin/opd_visits/table.vitals') }}</th>
                            <td>
                                <ul class="list-unstyled" style="margin-bottom: 0;">
                                    <li>{{ trans('admin/opd_visits/table.blood_pressure') }}: {{ $visit->blood_pressure ?: '—' }}</li>
                                    <li>{{ trans('admin/opd_visits/table.pulse_rate') }}: {{ $visit->pulse_rate ?? '—' }}</li>
                                    <li>{{ trans('admin/opd_visits/table.temperature') }}: {{ $visit->temperature ?? '—' }}</li>
                                    <li>{{ trans('admin/opd_visits/table.weight_kg') }}: {{ $visit->weight_kg ?? '—' }}</li>
                                    <li>{{ trans('admin/opd_visits/table.height_cm') }}: {{ $visit->height_cm ?? '—' }}</li>
                                </ul>
                            </td>
                        </tr>
                        @if ($visit->assessment)
                            <tr>
                                <th>{{ trans('admin/opd_visits/table.assessment') }}</th>
                                <td>{{ $visit->assessment }}</td>
                            </tr>
                        @endif
                        @if ($visit->diagnosis)
                            <tr>
                                <th>{{ trans('admin/opd_visits/table.diagnosis') }}</th>
                                <td>{{ $visit->diagnosis }}</td>
                            </tr>
                        @endif
                        @if ($visit->rest_days !== null)
                            <tr>
                                <th>{{ trans('admin/opd_visits/med_cert.rest_days') }}</th>
                                <td>{{ $visit->rest_days }}</td>
                            </tr>
                        @endif
                        @if ($visit->med_cert_remarks)
                            <tr>
                                <th>{{ trans('admin/opd_visits/med_cert.med_cert_remarks') }}</th>
                                <td style="white-space: pre-wrap;">{{ $visit->med_cert_remarks }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>{{ trans('admin/opd_visits/table.billing') }}</th>
                            <td>
                                @if ($visit->activeBillingInvoice)
                                    <a href="{{ route('billing-invoices.show', $visit->activeBillingInvoice) }}">
                                        {{ $visit->activeBillingInvoice->invoice_number }}
                                    </a>
                                    — {{ \App\Models\BillingInvoice::statusOptions()[$visit->activeBillingInvoice->status] ?? $visit->activeBillingInvoice->status }}
                                    (₱{{ number_format($visit->activeBillingInvoice->balance, 2) }} {{ trans('admin/billing_invoices/table.balance') }})
                                @else
                                    <span class="text-muted">{{ trans('admin/opd_visits/table.no_invoice') }}</span>
                                @endif
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    @if ($visit->labOrders->count())
                        <hr>
                        <h4 class="ahop-section-title">{{ trans('admin/opd_visits/table.lab_orders') }}</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ trans('admin/lab_orders/table.order_number') }}</th>
                                    <th>{{ trans('admin/lab_orders/table.test_panel') }}</th>
                                    <th>{{ trans('admin/lab_orders/table.status') }}</th>
                                    <th>{{ trans('admin/lab_orders/table.ordered_at') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($visit->labOrders as $order)
                                    <tr>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ \App\Models\LabOrder::testPanelOptions()[$order->test_panel] ?? $order->test_panel }}</td>
                                        <td>{{ \App\Models\LabOrder::statusOptions()[$order->status] ?? $order->status }}</td>
                                        <td>{{ $order->ordered_at?->format('Y-m-d H:i') }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('lab-orders.show', $order) }}" class="btn btn-xs btn-default">{{ trans('general.view') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="box-footer text-right">
                    <a href="{{ route('opd-visits.index') }}" class="btn btn-default">{{ trans('general.back') }}</a>
                </div>
            </div>
        </div>
    </div>
@stop
