@extends('layouts/default')

@section('title')
    {{ trans('admin/opd_visits/table.queue_title') }}
    @parent
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default ahop-panel">
            <div class="box-header with-border">
                <h2 class="box-title">{{ trans('admin/opd_visits/table.queue_title') }}</h2>
                <div class="box-tools pull-right">
                    <a href="{{ route('opd-visits.index') }}" class="btn btn-default btn-sm">{{ trans('general.opd_visits') }}</a>
                    @can('create', \App\Models\OpdVisit::class)
                        <a href="{{ route('opd-visits.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus" aria-hidden="true"></i> {{ trans('admin/opd_visits/table.create') }}
                        </a>
                    @endcan
                </div>
            </div>
            <div class="box-body">
                <p class="text-muted">{{ trans('admin/opd_visits/table.queue_subtitle') }}</p>

                <form method="get" action="{{ route('opd-visits.queue') }}" class="form-inline" style="margin-bottom: 16px;">
                    <div class="form-group">
                        <label for="date">{{ trans('general.date') }}</label>
                        <input type="date" name="date" id="date" class="form-control input-sm" value="{{ $day->format('Y-m-d') }}">
                    </div>
                    <button type="submit" class="btn btn-sm btn-default" style="margin-left: 8px;">{{ trans('admin/opd_visits/table.queue_show_date') }}</button>
                </form>

                @if ($queue->count())
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>{{ trans('admin/opd_visits/table.visit_date') }}</th>
                                <th>{{ trans('admin/opd_visits/table.patient') }}</th>
                                <th>{{ trans('admin/opd_visits/table.physician') }}</th>
                                <th>{{ trans('admin/opd_visits/table.status') }}</th>
                                <th>{{ trans('admin/opd_visits/table.chief_complaint') }}</th>
                                <th class="text-right">{{ trans('table.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($queue as $visit)
                                <tr>
                                    <td>{{ $visit->visit_date?->format('H:i') }}</td>
                                    <td>
                                        @if ($visit->patient)
                                            <a href="{{ route('patients.show', $visit->patient) }}">{{ $visit->patient->full_name }}</a>
                                            <br><small class="text-muted">{{ $visit->patient->patient_number }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $visit->physician?->present()->fullName ?? '—' }}</td>
                                    <td>{{ \App\Models\OpdVisit::statusOptions()[$visit->status] ?? $visit->status }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($visit->chief_complaint, 60) ?: '—' }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('opd-visits.show', $visit) }}" class="btn btn-xs btn-default">{{ trans('admin/opd_visits/table.open_visit') }}</a>
                                        @can('update', $visit)
                                            @if ($visit->status === \App\Models\OpdVisit::STATUS_SCHEDULED)
                                                <form method="post" action="{{ route('opd-visits.status', $visit) }}" style="display:inline;">
                                                    @csrf
                                                    <input type="hidden" name="status" value="{{ \App\Models\OpdVisit::STATUS_IN_PROGRESS }}">
                                                    <button type="submit" class="btn btn-xs btn-primary">{{ trans('admin/opd_visits/table.start_visit') }}</button>
                                                </form>
                                            @elseif ($visit->status === \App\Models\OpdVisit::STATUS_IN_PROGRESS)
                                                <form method="post" action="{{ route('opd-visits.status', $visit) }}" style="display:inline;">
                                                    @csrf
                                                    <input type="hidden" name="status" value="{{ \App\Models\OpdVisit::STATUS_COMPLETED }}">
                                                    <button type="submit" class="btn btn-xs btn-success">{{ trans('admin/opd_visits/table.complete_visit') }}</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">{{ trans('admin/opd_visits/table.no_queue') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
