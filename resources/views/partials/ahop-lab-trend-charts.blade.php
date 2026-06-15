@if (!empty($labChartData))
    <div class="row ahop-clinical-analytics-charts ahop-lab-charts">
        <div class="col-md-4">
            <div class="box box-default ahop-panel">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('admin/ai_insights/general.lab_charts_summary') }}</h3>
                </div>
                <div class="box-body">
                    <div class="ahop-chart-canvas-wrap">
                        <canvas id="ahopLabTrendSummaryChart" aria-label="{{ trans('admin/ai_insights/general.lab_charts_summary') }}"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="box box-default ahop-panel">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('admin/ai_insights/general.lab_charts_timeline') }}</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        @foreach ($labChartData['lineCharts'] as $chart)
                            <div class="col-md-6" style="margin-bottom: 20px;">
                                <div class="ahop-lab-line-chart-wrap">
                                    <h5 class="ahop-lab-chart-title">
                                        {{ $chart['title'] }}
                                        <span class="ahop-ai-trend ahop-ai-trend-{{ $chart['direction'] }}">{{ trans('admin/ai_insights/general.trend_'.$chart['direction']) }}</span>
                                    </h5>
                                    <div class="ahop-chart-canvas-wrap" style="height: 160px;">
                                        <canvas id="{{ $chart['id'] }}" aria-label="{{ $chart['title'] }}"></canvas>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
