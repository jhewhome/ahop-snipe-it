@extends('layouts/default')

@section('title')
    {{ $appointment->display_name }}
    @parent
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default ahop-panel ahop-clinical-detail-panel">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('admin/appointments/table.appointment_number') }}: {{ $appointment->appointment_number }}</h2>
                    <div class="box-tools pull-right">
                        @can('create', \App\Models\BillingInvoice::class)
                            @php($billingInvoice = $appointment->resolvedBillingInvoice())
                            @if ($billingInvoice)
                                <a href="{{ route('billing-invoices.show', $billingInvoice) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-file-invoice-dollar" aria-hidden="true"></i> {{ trans('admin/appointments/table.view_invoice') }}
                                </a>
                            @else
                                <form method="post" action="{{ route('appointments.billing-invoice', $appointment) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin/appointments/message.billing.confirm') }}');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-file-invoice-dollar" aria-hidden="true"></i> {{ trans('admin/appointments/table.create_invoice') }}
                                    </button>
                                </form>
                            @endif
                        @endcan
                        @if ($appointment->canCheckIn())
                            @can('update', $appointment)
                                <form method="post" action="{{ route('appointments.check-in', $appointment) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin/appointments/message.check_in.confirm') }}');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-door-open"></i> {{ trans('admin/appointments/table.check_in') }}
                                    </button>
                                </form>
                            @endcan
                        @endif
                        @if ($appointment->status === \App\Models\Appointment::STATUS_SCHEDULED)
                            @can('update', $appointment)
                                <form method="post" action="{{ route('appointments.send-reminder', $appointment) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin/appointments/message.reminder.confirm') }}');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-info">
                                        <i class="fas fa-envelope"></i> {{ trans('admin/appointments/table.send_reminder') }}
                                    </button>
                                </form>
                            @endcan
                        @endif
                        @can('update', $appointment)
                            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-pencil"></i> {{ trans('general.edit') }}
                            </a>
                        @endcan
                        @can('delete', $appointment)
                            <form method="post" action="{{ route('appointments.destroy', $appointment) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin/appointments/message.delete.confirm') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        @endcan
                    </div>
                </div>
                <div class="box-body">
                    <table class="table">
                        <tbody>
                        <tr>
                            <th style="width: 30%;">{{ trans('admin/appointments/table.patient') }}</th>
                            <td>
                                @if ($appointment->patient)
                                    <a href="{{ route('patients.show', $appointment->patient) }}">
                                        {{ $appointment->patient->full_name }} ({{ $appointment->patient->patient_number }})
                                    </a>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/appointments/table.scheduled_at') }}</th>
                            <td>{{ $appointment->scheduled_at?->format('Y-m-d H:i') }} ({{ $appointment->duration_minutes }} min)</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/appointments/table.visit_type') }}</th>
                            <td>{{ \App\Models\Appointment::visitTypeOptions()[$appointment->visit_type] ?? $appointment->visit_type }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/appointments/table.status') }}</th>
                            <td><span class="ahop-badge ahop-badge-{{ $appointment->status }}">{{ \App\Models\Appointment::statusOptions()[$appointment->status] ?? $appointment->status }}</span></td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/appointments/table.physician') }}</th>
                            <td>{{ $appointment->physician?->present()->fullName ?? '—' }}</td>
                        </tr>
                        @if ($appointment->opdVisit)
                            <tr>
                                <th>{{ trans('admin/appointments/table.opd_visit') }}</th>
                                <td>
                                    <a href="{{ route('opd-visits.show', $appointment->opdVisit) }}">{{ $appointment->opdVisit->visit_number }}</a>
                                </td>
                            </tr>
                        @endif
                        @if ($appointment->reason)
                            <tr>
                                <th>{{ trans('admin/appointments/table.reason') }}</th>
                                <td>{{ $appointment->reason }}</td>
                            </tr>
                        @endif
                        @if ($appointment->notes)
                            <tr>
                                <th>{{ trans('admin/appointments/table.notes') }}</th>
                                <td>{{ $appointment->notes }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>{{ trans('admin/appointments/table.reminder_status') }}</th>
                            <td>
                                @if ($appointment->reminder_sent_at)
                                    {{ trans('admin/appointments/message.reminder.already_sent') }}
                                    — {{ $appointment->reminder_sent_at->format('Y-m-d H:i') }}
                                @else
                                    {{ trans('admin/appointments/message.reminder.not_sent') }}
                                @endif
                                @if ($appointment->patient && empty($appointment->patient->email))
                                    <br><small class="text-muted">{{ trans('admin/appointments/message.reminder.no_email') }}</small>
                                @endif
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
