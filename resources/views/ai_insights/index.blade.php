@extends('layouts/default')

@section('title')
    {{ trans('admin/ai_insights/general.title') }}
    @parent
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default ahop-panel">
            <div class="box-header with-border">
                <h2 class="box-title">{{ trans('admin/ai_insights/general.title') }}</h2>
            </div>
            <div class="box-body">
                <p class="text-muted">{{ trans('admin/ai_insights/general.subtitle') }}</p>
                <p class="text-warning"><small><i class="fas fa-info-circle" aria-hidden="true"></i> {{ trans('admin/ai_insights/general.disclaimer') }}</small></p>
            </div>
        </div>
    </div>
</div>

<div class="nav-tabs-custom ahop-clinical-analytics-tabs">
    <ul class="nav nav-tabs">
    @can('patients.view')
        <li class="{{ $tab === 'patients' ? 'active' : '' }}"><a href="{{ route('clinical-analytics.index', ['tab' => 'patients']) }}">{{ trans('admin/ai_insights/general.tab_patients') }}</a></li>
    @endcan
    @can('lab_orders.view')
        <li class="{{ $tab === 'labs' ? 'active' : '' }}"><a href="{{ route('clinical-analytics.index', ['tab' => 'labs']) }}">{{ trans('admin/ai_insights/general.tab_labs') }}</a></li>
    @endcan
    @if(auth()->user()->hasAccess('assets.view'))
        <li class="{{ $tab === 'equipment' ? 'active' : '' }}"><a href="{{ route('clinical-analytics.index', ['tab' => 'equipment']) }}">{{ trans('admin/ai_insights/general.tab_equipment') }}</a></li>
    @endif
    </ul>
</div>

@if ($tab === 'patients')
@if (!empty($patientChartData))
    @include('partials.ahop-clinical-analytics-risk-charts', ['patientChartData' => $patientChartData])
@endif
<div class="row">
    <div class="col-md-12">
        <div class="box box-default ahop-panel">
            <div class="box-body">
                @if (count($patientRisks))
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>{{ trans('admin/ai_insights/general.patient') }}</th>
                                <th>{{ trans('admin/ai_insights/general.risk_score') }}</th>
                                <th></th>
                                <th>{{ trans('admin/ai_insights/general.risk_factors') }}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($patientRisks as $risk)
                                <tr>
                                    <td>
                                        <a href="{{ route('patients.show', $risk['patient_id']) }}">{{ $risk['full_name'] }}</a>
                                        <br><small class="text-muted">{{ $risk['patient_number'] }}</small>
                                    </td>
                                    <td><strong>{{ $risk['score'] }}</strong>/100</td>
                                    <td><span class="ahop-ai-risk ahop-ai-risk-{{ $risk['level'] }}">{{ $risk['level_label'] }}</span></td>
                                    <td>
                                        @if (count($risk['factors']))
                                            <ul class="list-unstyled" style="margin:0;font-size:12px;">
                                                @foreach (array_slice($risk['factors'], 0, 3) as $factor)
                                                    <li>{{ $factor }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted">{{ trans('admin/ai_insights/general.no_risk_factors') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('clinical-analytics.patient', $risk['patient_id']) }}" class="btn btn-xs btn-default">{{ trans('general.view') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">{{ trans('admin/ai_insights/general.no_data') }}</p>
                    <p class="text-muted"><small>{{ trans('admin/ai_insights/general.seed_demo_hint') }}</small></p>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@if ($tab === 'labs')
@if (count($labTrends) && !empty($labChartData))
    @include('partials.ahop-lab-trend-charts', ['labChartData' => $labChartData])
@endif
<div class="row">
    <div class="col-md-12">
        <div class="box box-default ahop-panel">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('admin/ai_insights/general.tab_labs') }}</h3>
                <div class="box-tools pull-right">
                    <form method="get" action="{{ route('clinical-analytics.index') }}" class="form-inline" style="display: inline-block; margin-right: 8px;">
                        <input type="hidden" name="tab" value="labs">
                        <select name="months" class="form-control input-sm">
                            @foreach ([3, 6, 12, 24] as $m)
                                <option value="{{ $m }}" {{ ($labMonths ?? 12) == $m ? 'selected' : '' }}>{{ $m }} {{ trans('general.months') }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-default">{{ trans('admin/ai_insights/general.apply_filter') }}</button>
                    </form>
                    <a href="{{ route('clinical-analytics.lab-trends.export', ['months' => $labMonths ?? 12]) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-download" aria-hidden="true"></i> {{ trans('admin/ai_insights/general.export_csv') }}
                    </a>
                </div>
            </div>
            <div class="box-body">
                @if (count($labTrends))
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>{{ trans('admin/ai_insights/general.test') }}</th>
                                <th>{{ trans('admin/ai_insights/general.patient') }}</th>
                                <th>{{ trans('admin/ai_insights/general.previous') }}</th>
                                <th>{{ trans('admin/ai_insights/general.latest') }}</th>
                                <th>{{ trans('admin/ai_insights/general.change') }}</th>
                                <th>{{ trans('admin/ai_insights/general.trend') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($labTrends as $trend)
                                <tr>
                                    <td>{{ $trend['test_name'] }}</td>
                                    <td>
                                        @if ($trend['patient_id'])
                                            <a href="{{ route('patients.show', $trend['patient_id']) }}">{{ $trend['patient_name'] }}</a>
                                        @endif
                                    </td>
                                    <td>{{ $trend['previous_value'] }} {{ $trend['unit'] }}</td>
                                    <td><strong>{{ $trend['latest_value'] }}</strong> {{ $trend['unit'] }}
                                        @if ($trend['latest_flag']) <span class="ahop-badge ahop-badge-{{ $trend['latest_flag'] === 'critical' ? 'urgent' : 'in_progress' }}">{{ $trend['latest_flag'] }}</span> @endif
                                    </td>
                                    <td>
                                        @if ($trend['delta_percent'] !== null)
                                            {{ $trend['delta'] > 0 ? '+' : '' }}{{ $trend['delta'] }} ({{ $trend['delta_percent'] }}%)
                                        @endif
                                    </td>
                                    <td><span class="ahop-ai-trend ahop-ai-trend-{{ $trend['direction'] }}">{{ $trend['direction_label'] }}</span>
                                        <br><small>{{ $trend['interpretation'] }}</small>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">{{ trans('admin/ai_insights/general.no_data') }}</p>
                    <p class="text-muted"><small>{{ trans('admin/ai_insights/general.seed_demo_hint') }}</small></p>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@if ($tab === 'equipment')
@if (!empty($equipmentChartData))
    @include('partials.ahop-clinical-analytics-equipment-charts', ['equipmentChartData' => $equipmentChartData])
@endif
<div class="row">
    <div class="col-md-12">
        <div class="box box-default ahop-panel">
            <div class="box-body">
                @if (count($equipmentPredictions))
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>{{ trans('admin/ai_insights/general.equipment') }}</th>
                                <th>{{ trans('admin/ai_insights/general.priority') }}</th>
                                <th>{{ trans('admin/ai_insights/general.due') }}</th>
                                <th>{{ trans('admin/ai_insights/general.recommendation') }}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($equipmentPredictions as $item)
                                <tr>
                                    <td>
                                        <a href="{{ route('hardware.show', $item['asset_id']) }}">{{ $item['asset_tag'] }}</a>
                                        <br><small class="text-muted">{{ $item['name'] }} — {{ $item['status'] }}</small>
                                    </td>
                                    <td>
                                        <span class="ahop-ai-urgency ahop-ai-urgency-{{ $item['urgency'] }}">{{ $item['urgency_label'] }}</span>
                                        <br><small>Score {{ $item['priority_score'] }}</small>
                                    </td>
                                    <td>{{ $item['due_label'] }}</td>
                                    <td><small>{{ $item['recommendation'] }}</small></td>
                                    <td class="text-right">
                                        <a href="{{ route('maintenances.index') }}" class="btn btn-xs btn-default">{{ trans('general.maintenances') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">{{ trans('admin/ai_insights/general.no_data') }}</p>
                    <p class="text-muted"><small>{{ trans('admin/ai_insights/general.seed_demo_hint') }}</small></p>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

@push('js')
    @include('partials.ahop-clinical-analytics-charts-script', [
        'patientChartData' => $patientChartData ?? null,
        'equipmentChartData' => $equipmentChartData ?? null,
        'labChartData' => $labChartData ?? null,
    ])
@endpush
@stop
