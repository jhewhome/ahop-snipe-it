@if (!empty($equipmentChartData))
<div class="row ahop-clinical-analytics-charts">
    <div class="col-md-4">
        <div class="box box-default ahop-panel">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('admin/ai_insights/general.equipment_charts_summary') }}</h3>
            </div>
            <div class="box-body">
                <div class="ahop-chart-canvas-wrap">
                    <canvas id="ahopEquipmentSummaryChart" aria-label="{{ trans('admin/ai_insights/general.equipment_charts_summary') }}"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="box box-default ahop-panel">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('admin/ai_insights/general.equipment_charts_top') }}</h3>
            </div>
            <div class="box-body">
                <div class="ahop-chart-canvas-wrap">
                    <canvas id="ahopEquipmentTopChart" aria-label="{{ trans('admin/ai_insights/general.equipment_charts_top') }}"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
