@extends('layouts/default')

@section('title')
    {{ trans('general.appointments') }}
    @parent
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default ahop-panel ahop-appointments-panel">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('general.appointments') }}</h2>
                    <div class="box-tools pull-right">
                        @can('view', \App\Models\Appointment::class)
                            <a href="{{ route('appointments.calendar') }}" class="btn btn-default">
                                <i class="fas fa-calendar" aria-hidden="true"></i> {{ trans('admin/appointments/table.calendar') }}
                            </a>
                        @endcan
                        @can('create', \App\Models\Appointment::class)
                            <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus" aria-hidden="true"></i> {{ trans('admin/appointments/table.create') }}
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="box-body">
                    @if ($todayQueue->count())
                        <h4 class="ahop-appointments-section-title">{{ trans('admin/appointments/table.today_queue') }}</h4>
                        <div class="table-responsive ahop-appointments-queue">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ trans('admin/appointments/table.scheduled_at') }}</th>
                                    <th>{{ trans('admin/appointments/table.patient') }}</th>
                                    <th>{{ trans('admin/appointments/table.physician') }}</th>
                                    <th>{{ trans('admin/appointments/table.status') }}</th>
                                    <th class="text-right">{{ trans('table.actions') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($todayQueue as $appt)
                                    <tr>
                                        <td>{{ $appt->scheduled_at?->format('H:i') }}</td>
                                        <td>
                                            @if ($appt->patient)
                                                <a href="{{ route('patients.show', $appt->patient) }}">{{ $appt->patient->full_name }}</a>
                                            @endif
                                        </td>
                                        <td>{{ $appt->physician?->present()->fullName ?? '—' }}</td>
                                        <td><span class="ahop-badge ahop-badge-{{ $appt->status }}">{{ \App\Models\Appointment::statusOptions()[$appt->status] ?? $appt->status }}</span></td>
                                        <td class="text-right">
                                            @can('view', $appt)
                                                <a href="{{ route('appointments.show', $appt) }}" class="btn btn-sm btn-default"><i class="fas fa-eye"></i></a>
                                            @endcan
                                            @if ($appt->canCheckIn())
                                                @can('update', $appt)
                                                    <form method="post" action="{{ route('appointments.check-in', $appt) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin/appointments/message.check_in.confirm') }}');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">{{ trans('admin/appointments/table.check_in') }}</button>
                                                    </form>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <hr>
                    @endif

                    <form method="get" action="{{ route('appointments.index') }}" class="form-inline ahop-appointments-filters">
                        <div class="form-group">
                            <input type="date" name="date" class="form-control" value="{{ request('date', $day->format('Y-m-d')) }}">
                        </div>
                        <div class="form-group" style="margin-left: 8px;">
                            <input type="text" name="search" class="form-control" placeholder="{{ trans('general.search') }}" value="{{ request('search') }}">
                        </div>
                        <div class="form-group" style="margin-left: 8px;">
                            <select name="status" class="form-control">
                                <option value="">{{ trans('admin/appointments/table.status') }} — {{ trans('general.all') }}</option>
                                @foreach (\App\Models\Appointment::statusOptions() as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-default" style="margin-left: 8px;">{{ trans('general.search') }}</button>
                    </form>

                    <h4 class="ahop-appointments-section-title">{{ trans('admin/appointments/table.list_for_day', ['date' => $day->format('M j, Y')]) }}</h4>

                    <div class="table-responsive">
                        <table class="table table-bordered snipe-table">
                            <thead>
                            <tr>
                                <th>{{ trans('admin/appointments/table.appointment_number') }}</th>
                                <th>{{ trans('admin/appointments/table.scheduled_at') }}</th>
                                <th>{{ trans('admin/appointments/table.patient') }}</th>
                                <th>{{ trans('admin/appointments/table.visit_type') }}</th>
                                <th>{{ trans('admin/appointments/table.status') }}</th>
                                <th class="text-right">{{ trans('table.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($appointments as $appt)
                                <tr>
                                    <td><a href="{{ route('appointments.show', $appt) }}">{{ $appt->appointment_number }}</a></td>
                                    <td>{{ $appt->scheduled_at?->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @if ($appt->patient)
                                            <a href="{{ route('patients.show', $appt->patient) }}">{{ $appt->patient->full_name }}</a>
                                        @endif
                                    </td>
                                    <td>{{ \App\Models\Appointment::visitTypeOptions()[$appt->visit_type] ?? $appt->visit_type }}</td>
                                    <td><span class="ahop-badge ahop-badge-{{ $appt->status }}">{{ \App\Models\Appointment::statusOptions()[$appt->status] ?? $appt->status }}</span></td>
                                    <td class="text-right">
                                        @can('view', $appt)
                                            <a href="{{ route('appointments.show', $appt) }}" class="btn btn-sm btn-default"><i class="fas fa-eye"></i></a>
                                        @endcan
                                        @can('update', $appt)
                                            <a href="{{ route('appointments.edit', $appt) }}" class="btn btn-sm btn-warning"><i class="fas fa-pencil"></i></a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr class="ahop-empty-row">
                                    <td colspan="6">
                                        @include('partials.ahop-empty-state', [
                                            'icon' => 'fa-calendar-day',
                                            'title' => trans('ahop.empty_appointments_title'),
                                            'message' => trans('ahop.empty_appointments_message'),
                                            'actionUrl' => auth()->user()->can('create', \App\Models\Appointment::class) ? route('appointments.create') : null,
                                            'actionLabel' => auth()->user()->can('create', \App\Models\Appointment::class) ? trans('admin/appointments/table.create') : null,
                                            'compact' => true,
                                        ])
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $appointments->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
