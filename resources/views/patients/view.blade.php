@extends('layouts/default')

@section('title')
    {{ $patient->display_name }}
    @parent
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default ahop-panel ahop-clinical-detail-panel ahop-patients-panel">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ $patient->full_name }}</h2>
                    <div class="box-tools pull-right">
                        @can('view', $patient)
                            <a href="{{ route('patients.clinical-summary', $patient) }}" class="btn btn-sm btn-default" target="_blank">
                                <i class="fas fa-file-medical" aria-hidden="true"></i> {{ trans('admin/patients/table.print_summary') }}
                            </a>
                        @endcan
                        @can('update', $patient)
                            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-pencil" aria-hidden="true"></i> {{ trans('general.edit') }}
                            </a>
                        @endcan
                        @can('delete', $patient)
                            <form method="post" action="{{ route('patients.destroy', $patient) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin/patients/message.delete.confirm') }}');">
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
                            <th style="width: 35%;">{{ trans('admin/patients/table.patient_number') }}</th>
                            <td>{{ $patient->patient_number }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/patients/table.full_name') }}</th>
                            <td>{{ $patient->full_name }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/patients/table.sex') }}</th>
                            <td>{{ $patient->sex === 'M' ? 'Male' : 'Female' }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/patients/table.birthdate') }}</th>
                            <td>{{ $patient->birthdate ? $patient->birthdate->format('Y-m-d') : '' }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/patients/table.contact_number') }}</th>
                            <td>{{ $patient->contact_number ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/patients/table.email') }}</th>
                            <td>{{ $patient->email ?: '—' }}</td>
                        </tr>
                        @if ($patient->company_id && ($company = $patient->company ?? \App\Models\Company::find($patient->company_id)))
                            <tr>
                                <th>{{ trans('admin/opd_visits/table.clinic_site') }}</th>
                                <td>{{ $company->name }}</td>
                            </tr>
                        @endif
                        @if ($patient->notes)
                            <tr>
                                <th>{{ trans('admin/patients/table.notes') }}</th>
                                <td>{{ $patient->notes }}</td>
                            </tr>
                        @endif
                        @if ($patient->allergies)
                            <tr>
                                <th>{{ trans('admin/patients/table.allergies') }}</th>
                                <td>{{ $patient->allergies }}</td>
                            </tr>
                        @endif
                        @if ($patient->problem_list)
                            <tr>
                                <th>{{ trans('admin/patients/table.problem_list') }}</th>
                                <td style="white-space: pre-wrap;">{{ $patient->problem_list }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>{{ trans('general.created_at') }}</th>
                            <td>{{ $patient->created_at }}</td>
                        </tr>
                        </tbody>
                    </table>

                    @if (!empty($patientRisk))
                        @include('partials.ahop-patient-risk-panel', [
                            'risk' => $patientRisk,
                            'patient' => $patient,
                            'compact' => true,
                        ])
                    @endif

                    <hr>
                    <h4 class="ahop-section-title">{{ trans('admin/patients/table.documents_title') }}</h4>
                    <p class="text-muted"><small>{{ trans('admin/patients/table.documents_help') }}</small></p>

                    <div style="margin-bottom: 14px;">
                        @can('view', $patient)
                            <a href="{{ route('patients.clinical-summary', $patient) }}" class="btn btn-sm btn-default" target="_blank">
                                <i class="fas fa-file-medical" aria-hidden="true"></i> {{ trans('admin/patients/table.print_summary') }}
                            </a>
                        @endcan
                    </div>

                    <p style="margin-bottom: 8px;"><strong>{{ trans('admin/patients/table.payment_receipts') }}</strong></p>
                    @if ($patient->billingInvoices->count() > 0)
                        <ul class="list-unstyled" style="margin-bottom: 14px;">
                            @foreach ($patient->billingInvoices as $invoice)
                                @can('view', $invoice)
                                    <li style="margin-bottom: 6px;">
                                        <a href="{{ route('billing-invoices.receipt', $invoice) }}" class="btn btn-xs btn-default" target="_blank">
                                            <i class="fas fa-print" aria-hidden="true"></i>
                                            {{ $invoice->invoice_number }}
                                            @if ($invoice->issued_at)
                                                <span class="text-muted">({{ $invoice->issued_at->format('Y-m-d') }})</span>
                                            @endif
                                        </a>
                                    </li>
                                @endcan
                            @endforeach
                        </ul>
                        @can('view', \App\Models\BillingInvoice::class)
                            <p style="margin-bottom: 14px;">
                                <a href="{{ route('billing-invoices.index', ['patient_id' => $patient->id]) }}">{{ trans('general.viewall') }}</a>
                            </p>
                        @endcan
                    @else
                        <p class="text-muted"><small>{{ trans('admin/patients/table.no_payment_receipts') }}</small></p>
                    @endif

                    <p class="text-muted"><small><i class="fas fa-info-circle" aria-hidden="true"></i> {{ trans('admin/patients/table.med_cert_note') }}</small></p>

                    <hr>
                    <h4 class="ahop-section-title">{{ trans('admin/clinical_reports/timeline.title') }}</h4>
                    @if (count($timeline))
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <thead>
                                <tr>
                                    <th style="width: 18%;">{{ trans('general.date') }}</th>
                                    <th style="width: 18%;">{{ trans('general.type') }}</th>
                                    <th>{{ trans('general.details') }}</th>
                                    <th style="width: 10%;"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($timeline as $event)
                                    <tr>
                                        <td>{{ $event['at']?->format('Y-m-d H:i') }}</td>
                                        <td>{{ $event['label'] }}</td>
                                        <td>{{ $event['detail'] }}</td>
                                        <td class="text-right">
                                            @if ($event['url'])
                                                <a href="{{ $event['url'] }}" class="btn btn-xs btn-default">{{ trans('general.view') }}</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">{{ trans('admin/clinical_reports/timeline.empty') }}</p>
                    @endif

                    <hr>
                    <h4 class="ahop-section-title">{{ trans('general.appointments') }}</h4>
                    @can('create', \App\Models\Appointment::class)
                        <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary" style="margin-bottom: 10px;">
                            <i class="fas fa-plus" aria-hidden="true"></i> {{ trans('admin/appointments/table.create') }}
                        </a>
                    @endcan

                    @if ($patient->appointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ trans('admin/appointments/table.appointment_number') }}</th>
                                    <th>{{ trans('admin/appointments/table.scheduled_at') }}</th>
                                    <th>{{ trans('admin/appointments/table.status') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($patient->appointments as $appointment)
                                    <tr>
                                        <td>{{ $appointment->appointment_number }}</td>
                                        <td>{{ $appointment->scheduled_at?->format('Y-m-d H:i') }}</td>
                                        <td>{{ \App\Models\Appointment::statusOptions()[$appointment->status] ?? $appointment->status }}</td>
                                        <td class="text-right">
                                            @can('view', $appointment)
                                                <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-xs btn-default">{{ trans('general.view') }}</a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @can('view', \App\Models\Appointment::class)
                            <a href="{{ route('appointments.index', ['search' => $patient->patient_number]) }}">{{ trans('general.viewall') }}</a>
                        @endcan
                    @else
                        <p class="text-muted">{{ trans('admin/appointments/table.no_appointments') }}</p>
                    @endif

                    <hr>
                    <h4 class="ahop-section-title">{{ trans('admin/opd_visits/table.recent_visits') }}</h4>
                    @can('create', \App\Models\OpdVisit::class)
                        <a href="{{ route('opd-visits.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary" style="margin-bottom: 10px;">
                            <i class="fas fa-plus" aria-hidden="true"></i> {{ trans('admin/opd_visits/table.create') }}
                        </a>
                    @endcan

                    @if ($patient->opdVisits->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ trans('admin/opd_visits/table.visit_number') }}</th>
                                    <th>{{ trans('admin/opd_visits/table.visit_date') }}</th>
                                    <th>{{ trans('admin/opd_visits/table.status') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($patient->opdVisits as $visit)
                                    <tr>
                                        <td>{{ $visit->visit_number }}</td>
                                        <td>{{ $visit->visit_date?->format('Y-m-d H:i') }}</td>
                                        <td>{{ \App\Models\OpdVisit::statusOptions()[$visit->status] ?? $visit->status }}</td>
                                        <td class="text-right">
                                            @can('view', $visit)
                                                <a href="{{ route('opd-visits.show', $visit) }}" class="btn btn-xs btn-default">{{ trans('general.view') }}</a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @can('view', \App\Models\OpdVisit::class)
                            <a href="{{ route('opd-visits.index', ['patient_id' => $patient->id]) }}">{{ trans('general.viewall') }}</a>
                        @endcan
                    @else
                        <p class="text-muted">{{ trans('admin/opd_visits/table.no_visits') }}</p>
                    @endif

                    <hr>
                    <h4 class="ahop-section-title">{{ trans('admin/lab_orders/table.recent_orders') }}</h4>
                    @can('create', \App\Models\LabOrder::class)
                        <a href="{{ route('lab-orders.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary" style="margin-bottom: 10px;">
                            <i class="fas fa-plus" aria-hidden="true"></i> {{ trans('admin/lab_orders/table.create') }}
                        </a>
                    @endcan

                    @if ($patient->labOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ trans('admin/lab_orders/table.order_number') }}</th>
                                    <th>{{ trans('admin/lab_orders/table.test_panel') }}</th>
                                    <th>{{ trans('admin/lab_orders/table.status') }}</th>
                                    <th>{{ trans('admin/lab_orders/table.results_count') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($patient->labOrders as $labOrder)
                                    <tr>
                                        <td>{{ $labOrder->order_number }}</td>
                                        <td>{{ \App\Models\LabOrder::testPanelOptions()[$labOrder->test_panel] ?? $labOrder->test_panel }}</td>
                                        <td>{{ \App\Models\LabOrder::statusOptions()[$labOrder->status] ?? $labOrder->status }}</td>
                                        <td>{{ $labOrder->results_count }}</td>
                                        <td class="text-right">
                                            @can('view', $labOrder)
                                                <a href="{{ route('lab-orders.show', $labOrder) }}" class="btn btn-xs btn-default">{{ trans('general.view') }}</a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @can('view', \App\Models\LabOrder::class)
                            <a href="{{ route('lab-orders.index', ['patient_id' => $patient->id]) }}">{{ trans('general.viewall') }}</a>
                        @endcan
                    @else
                        <p class="text-muted">{{ trans('admin/lab_orders/table.no_orders') }}</p>
                    @endif

                    <hr>
                    <h4 class="ahop-section-title">{{ trans('admin/billing_invoices/table.recent_invoices') }}</h4>
                    @can('create', \App\Models\BillingInvoice::class)
                        <a href="{{ route('billing-invoices.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary" style="margin-bottom: 10px;">
                            <i class="fas fa-plus" aria-hidden="true"></i> {{ trans('admin/billing_invoices/table.create') }}
                        </a>
                    @endcan

                    @if ($patient->billingInvoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ trans('admin/billing_invoices/table.invoice_number') }}</th>
                                    <th>{{ trans('admin/billing_invoices/table.status') }}</th>
                                    <th class="text-right">{{ trans('admin/billing_invoices/table.balance') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($patient->billingInvoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td>{{ \App\Models\BillingInvoice::statusOptions()[$invoice->status] ?? $invoice->status }}</td>
                                        <td class="text-right">₱{{ number_format($invoice->balance, 2) }}</td>
                                        <td class="text-right">
                                            @can('view', $invoice)
                                                <a href="{{ route('billing-invoices.show', $invoice) }}" class="btn btn-xs btn-default">{{ trans('general.view') }}</a>
                                                <a href="{{ route('billing-invoices.receipt', $invoice) }}" class="btn btn-xs btn-default" target="_blank" title="{{ trans('admin/billing_invoices/table.print_receipt') }}">
                                                    <i class="fas fa-print" aria-hidden="true"></i>
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @can('view', \App\Models\BillingInvoice::class)
                            <a href="{{ route('billing-invoices.index', ['patient_id' => $patient->id]) }}">{{ trans('general.viewall') }}</a>
                        @endcan
                    @else
                        <p class="text-muted">{{ trans('admin/billing_invoices/table.no_invoices') }}</p>
                    @endif
                </div>
                <div class="box-footer text-right">
                    <a href="{{ route('patients.index') }}" class="btn btn-default">{{ trans('general.back') }}</a>
                </div>
            </div>
        </div>
    </div>
@stop
