@extends('layouts/default')

@section('title')
    {{ trans('admin/appointments/table.calendar') }}
    @parent
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default ahop-panel">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('admin/appointments/table.calendar') }}</h2>
                    <div class="box-tools pull-right">
                        <a href="{{ route('appointments.index') }}" class="btn btn-default">{{ trans('general.appointments') }}</a>
                        @can('create', \App\Models\Appointment::class)
                            <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> {{ trans('admin/appointments/table.create') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="box-body">
                    <div class="btn-group" style="margin-bottom: 16px;">
                        <a href="{{ route('appointments.calendar', ['week' => $weekStart->copy()->subWeek()->format('Y-m-d')]) }}" class="btn btn-default">&larr; {{ trans('general.previous') }}</a>
                        <span class="btn btn-default disabled">{{ trans('admin/appointments/table.week_of', ['start' => $weekStart->format('M j'), 'end' => $weekEnd->format('M j, Y')]) }}</span>
                        <a href="{{ route('appointments.calendar', ['week' => $weekStart->copy()->addWeek()->format('Y-m-d')]) }}" class="btn btn-default">{{ trans('general.next') }} &rarr;</a>
                    </div>

                    <div class="row">
                        @foreach ($days as $day)
                            <div class="col-md-6 col-lg-4" style="margin-bottom: 16px;">
                                <div class="panel panel-default">
                                    <div class="panel-heading" style="background: var(--ahop-primary-dark, #094a52); color: #fff;">
                                        <strong>{{ $day['label'] }}</strong>
                                        <span class="badge pull-right">{{ $day['appointments']->count() }}</span>
                                    </div>
                                    <div class="panel-body" style="padding: 8px; min-height: 80px;">
                                        @forelse ($day['appointments'] as $appt)
                                            <div style="margin-bottom: 8px; padding: 8px; border-left: 3px solid var(--ahop-accent, #2eb8a6); background: #f4f8fa; border-radius: 4px;">
                                                <a href="{{ route('appointments.show', $appt) }}"><strong>{{ $appt->scheduled_at->format('H:i') }}</strong></a>
                                                — {{ $appt->patient?->full_name }}
                                                <br><small><span class="ahop-badge ahop-badge-{{ $appt->status }}">{{ \App\Models\Appointment::statusOptions()[$appt->status] ?? $appt->status }}</span></small>
                                            </div>
                                        @empty
                                            <p class="text-muted" style="margin: 0;">{{ trans('admin/appointments/table.no_appointments') }}</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
