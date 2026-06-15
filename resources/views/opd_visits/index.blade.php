@extends('layouts/default')

@section('title')
    {{ trans('general.opd_visits') }}
    @parent
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default ahop-panel">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('general.opd_visits') }}</h2>
                    <div class="box-tools pull-right">
                        @can('index', \App\Models\OpdVisit::class)
                            <a href="{{ route('opd-visits.queue') }}" class="btn btn-default">
                                <i class="fas fa-list-ol" aria-hidden="true"></i> {{ trans('admin/opd_visits/table.queue_title') }}
                            </a>
                        @endcan
                        @can('create', \App\Models\OpdVisit::class)
                            <a href="{{ route('opd-visits.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus" aria-hidden="true"></i> {{ trans('admin/opd_visits/table.create') }}
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="box-body">
                    <form method="get" action="{{ route('opd-visits.index') }}" class="form-inline" style="margin-bottom: 15px;">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control" placeholder="{{ trans('general.search') }}"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="form-group" style="margin-left: 8px;">
                            <select name="status" class="form-control">
                                <option value="">{{ trans('admin/opd_visits/table.status') }} — {{ trans('general.all') }}</option>
                                @foreach (\App\Models\OpdVisit::statusOptions() as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-default" style="margin-left: 8px;">{{ trans('general.search') }}</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered snipe-table">
                            <thead>
                            <tr>
                                <th>{{ trans('admin/opd_visits/table.visit_number') }}</th>
                                <th>{{ trans('admin/opd_visits/table.patient') }}</th>
                                <th>{{ trans('admin/opd_visits/table.visit_date') }}</th>
                                <th>{{ trans('admin/opd_visits/table.visit_type') }}</th>
                                <th>{{ trans('admin/opd_visits/table.status') }}</th>
                                <th>{{ trans('admin/opd_visits/table.physician') }}</th>
                                <th class="text-right">{{ trans('table.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($visits as $visit)
                                <tr>
                                    <td>
                                        <a href="{{ route('opd-visits.show', $visit) }}">{{ $visit->visit_number }}</a>
                                    </td>
                                    <td>
                                        @if ($visit->patient)
                                            <a href="{{ route('patients.show', $visit->patient) }}">{{ $visit->patient->full_name }}</a>
                                            <br><small class="text-muted">{{ $visit->patient->patient_number }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $visit->visit_date?->format('Y-m-d H:i') }}</td>
                                    <td>{{ \App\Models\OpdVisit::visitTypeOptions()[$visit->visit_type] ?? $visit->visit_type }}</td>
                                    <td><span class="ahop-badge ahop-badge-{{ $visit->status }}">{{ \App\Models\OpdVisit::statusOptions()[$visit->status] ?? $visit->status }}</span></td>
                                    <td>{{ $visit->physician?->present()->fullName ?? '—' }}</td>
                                    <td class="text-right">
                                        @can('view', $visit)
                                            <a href="{{ route('opd-visits.show', $visit) }}" class="btn btn-sm btn-default" title="{{ trans('general.view') }}">
                                                <i class="fas fa-eye" aria-hidden="true"></i>
                                            </a>
                                        @endcan
                                        @can('update', $visit)
                                            <a href="{{ route('opd-visits.edit', $visit) }}" class="btn btn-sm btn-warning" title="{{ trans('general.edit') }}">
                                                <i class="fas fa-pencil" aria-hidden="true"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr class="ahop-empty-row">
                                    <td colspan="7">
                                        @include('partials.ahop-empty-state', [
                                            'icon' => 'fa-stethoscope',
                                            'title' => trans('ahop.empty_opd_title'),
                                            'message' => trans('ahop.empty_opd_message'),
                                            'actionUrl' => auth()->user()->can('create', \App\Models\OpdVisit::class) ? route('opd-visits.create') : null,
                                            'actionLabel' => auth()->user()->can('create', \App\Models\OpdVisit::class) ? trans('admin/opd_visits/table.create') : null,
                                            'compact' => true,
                                        ])
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $visits->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
