<div class="row ahop-clinical-report-charts">
    <div class="col-md-6">
        <div class="box box-default ahop-panel">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('admin/clinical_reports/general.chart_opd_visits') }}</h3>
            </div>
            <div class="box-body">
                <div class="ahop-chart-canvas-wrap">
                    <canvas id="ahopReportOpdChart" aria-label="{{ trans('admin/clinical_reports/general.chart_opd_visits') }}"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="box box-default ahop-panel">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('admin/clinical_reports/general.chart_collections') }}</h3>
            </div>
            <div class="box-body">
                <div class="ahop-chart-canvas-wrap">
                    <canvas id="ahopReportCollectionsChart" aria-label="{{ trans('admin/clinical_reports/general.chart_collections') }}"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row ahop-clinical-report-charts">
    <div class="col-md-6">
        <div class="box box-default ahop-panel">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('admin/clinical_reports/general.chart_revenue_by_service') }}</h3>
            </div>
            <div class="box-body">
                <div class="ahop-chart-canvas-wrap">
                    <canvas id="ahopReportRevenueChart" aria-label="{{ trans('admin/clinical_reports/general.chart_revenue_by_service') }}"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="box box-default ahop-panel">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('admin/clinical_reports/general.chart_invoice_aging') }}</h3>
                <small class="text-muted">{{ trans('admin/clinical_reports/general.chart_invoice_aging_note') }}</small>
            </div>
            <div class="box-body">
                <div class="ahop-chart-canvas-wrap">
                    <canvas id="ahopReportAgingChart" aria-label="{{ trans('admin/clinical_reports/general.chart_invoice_aging') }}"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
