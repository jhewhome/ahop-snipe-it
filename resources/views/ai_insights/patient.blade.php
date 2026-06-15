@extends('layouts/default')

@section('title')
    {{ trans('admin/ai_insights/general.patient_detail') }} — {{ $patient->full_name }}
    @parent
@stop

@section('content')
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-default ahop-panel">
            <div class="box-header with-border">
                <h2 class="box-title">{{ $patient->full_name }} <small class="text-muted">{{ $patient->patient_number }}</small></h2>
                <div class="box-tools pull-right">
                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-default">{{ trans('admin/ai_insights/general.view_patient') }}</a>
                    <a href="{{ route('clinical-analytics.index', ['tab' => 'patients']) }}" class="btn btn-sm btn-default">{{ trans('general.back') }}</a>
                </div>
            </div>
            <div class="box-body">
                @include('partials.ahop-patient-risk-panel', [
                    'risk' => $risk,
                    'patient' => $patient,
                    'showLink' => false,
                ])

                @if (count($labTrends))
                    <hr>
                    <div class="clearfix" style="margin-bottom: 10px;">
                        <h4 class="ahop-section-title pull-left" style="margin-top: 0;">{{ trans('admin/ai_insights/general.tab_labs') }}</h4>
                        <a href="{{ route('clinical-analytics.lab-trends.export', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary pull-right">
                            <i class="fas fa-download" aria-hidden="true"></i> {{ trans('admin/ai_insights/general.export_csv') }}
                        </a>
                    </div>
                    @if (!empty($labChartData))
                        @include('partials.ahop-lab-trend-charts', ['labChartData' => $labChartData])
                    @endif
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>{{ trans('admin/ai_insights/general.test') }}</th>
                            <th>{{ trans('admin/ai_insights/general.trend') }}</th>
                            <th>{{ trans('admin/ai_insights/general.interpretation') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($labTrends as $trend)
                            <tr>
                                <td>{{ $trend['test_name'] }}</td>
                                <td><span class="ahop-ai-trend ahop-ai-trend-{{ $trend['direction'] }}">{{ $trend['direction_label'] }}</span></td>
                                <td>{{ $trend['interpretation'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>
@push('js')
    @include('partials.ahop-clinical-analytics-charts-script', ['labChartData' => $labChartData ?? null])
@endpush
@stop
