@extends('layouts/default')

@section('title')
    {{ trans('admin/clinical_reports/general.title') }}
    @parent
@stop

@section('content')
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-default ahop-panel">
            <div class="box-header with-border">
                <h2 class="box-title">{{ trans('admin/clinical_reports/general.title') }}</h2>
            </div>
            <div class="box-body">
                <p class="text-muted">{{ trans('admin/clinical_reports/general.intro') }}</p>

                <form method="get" action="{{ route('reports.clinical.index') }}" class="form-inline" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="from">{{ trans('admin/clinical_reports/general.from') }}</label>
                        <input type="date" name="from" id="from" class="form-control input-sm" value="{{ $from }}" required>
                    </div>
                    <div class="form-group" style="margin-left: 10px;">
                        <label for="to">{{ trans('admin/clinical_reports/general.to') }}</label>
                        <input type="date" name="to" id="to" class="form-control input-sm" value="{{ $to }}" required>
                    </div>
                    <button type="submit" class="btn btn-sm btn-default" style="margin-left: 10px;">{{ trans('admin/clinical_reports/general.apply') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@include('partials.ahop-clinical-report-charts', ['chartData' => $chartData ?? []])

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-default ahop-panel">
            <div class="box-body">
                <h3 class="ahop-section-title">{{ trans('admin/clinical_reports/general.exports_title') }}</h3>
                <p class="text-muted">{{ trans('admin/clinical_reports/general.exports_intro') }}</p>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>{{ trans('general.name') }}</th>
                            <th>{{ trans('general.description') }}</th>
                            <th class="text-right">{{ trans('admin/clinical_reports/general.export') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $reports = [
                                'daily_summary' => ['daily_summary', 'daily_summary_desc'],
                                'collections' => ['collections', 'collections_desc'],
                                'opd_visits' => ['opd_visits', 'opd_visits_desc'],
                                'lab_turnaround' => ['lab_turnaround', 'lab_turnaround_desc'],
                                'revenue_by_service' => ['revenue_by_service', 'revenue_by_service_desc'],
                                'physician_visits' => ['physician_visits', 'physician_visits_desc'],
                                'invoice_aging' => ['invoice_aging', 'invoice_aging_desc'],
                            ];
                        @endphp
                        @foreach ($reports as $type => [$titleKey, $descKey])
                            <tr>
                                <td><strong>{{ trans('admin/clinical_reports/general.'.$titleKey) }}</strong></td>
                                <td>
                                    {{ trans('admin/clinical_reports/general.'.$descKey) }}
                                    @if ($type === 'invoice_aging')
                                        <br><small class="text-muted">{{ trans('admin/clinical_reports/general.no_date_range') }}</small>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('reports.clinical.export', array_merge(['type' => $type], $type === 'invoice_aging' ? [] : ['from' => $from, 'to' => $to])) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download" aria-hidden="true"></i> {{ trans('admin/clinical_reports/general.export') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
    @include('partials.ahop-clinical-report-charts-script', ['chartData' => $chartData ?? []])
@endpush
@stop
