@extends('layouts/default')



@section('title')

    {{ trans('ahop.dashboard_title') }}

    @parent

@stop



@section('content')



<div class="row">

    <div class="col-md-12">

        <div class="box box-default ahop-dashboard-header ahop-dashboard-hero">

            <div class="box-body">

                <h2 class="ahop-dashboard-title">{{ trans('ahop.dashboard_title') }}</h2>

                <p class="ahop-dashboard-subtitle">
                    {{ trans('ahop.dashboard_subtitle') }}
                    @if (config('ahop.dashboard_auto_refresh.enabled'))
                        <span class="ahop-dashboard-refresh-meta" id="ahop-dashboard-refresh-meta" aria-live="polite"></span>
                    @endif
                </p>

            </div>

        </div>

    </div>

</div>



<div class="row ahop-dashboard-stats">

    @can('view', \App\Models\Patient::class)

        <div class="col-lg-2 col-md-4 col-sm-6 col-xs-6">

            <a href="{{ route('patients.index') }}" class="ahop-stat-card ahop-stat-patients">

                <div class="ahop-stat-value" data-ahop-stat="patients">{{ number_format($stats['patients']) }}</div>

                <div class="ahop-stat-label">{{ trans('ahop.stat_patients') }}</div>

                <i class="fas fa-user-injured ahop-stat-icon" aria-hidden="true"></i>

            </a>

        </div>

    @endcan



    @can('view', \App\Models\Appointment::class)

        <div class="col-lg-2 col-md-4 col-sm-6 col-xs-6">

            <a href="{{ route('appointments.index') }}" class="ahop-stat-card ahop-stat-appointments">

                <div class="ahop-stat-value" data-ahop-stat="appointments_today">{{ number_format($stats['appointments_today'] ?? 0) }}</div>

                <div class="ahop-stat-label">{{ trans('ahop.stat_appointments_today') }}</div>

                <i class="fas fa-calendar-check ahop-stat-icon" aria-hidden="true"></i>

            </a>

        </div>

    @endcan



    @can('view', \App\Models\OpdVisit::class)

        <div class="col-lg-2 col-md-4 col-sm-6 col-xs-6">

            <a href="{{ route('opd-visits.index') }}" class="ahop-stat-card ahop-stat-opd">

                <div class="ahop-stat-value" data-ahop-stat="opd_today">{{ number_format($stats['opd_today']) }}</div>

                <div class="ahop-stat-label">{{ trans('ahop.stat_opd_today') }}</div>

                <i class="fas fa-stethoscope ahop-stat-icon" aria-hidden="true"></i>

            </a>

        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 col-xs-6">

            <a href="{{ route('opd-visits.index', ['status' => 'in_progress']) }}" class="ahop-stat-card ahop-stat-opd-active">

                <div class="ahop-stat-value" data-ahop-stat="opd_in_progress">{{ number_format($stats['opd_in_progress']) }}</div>

                <div class="ahop-stat-label">{{ trans('ahop.stat_opd_active') }}</div>

                <i class="fas fa-heart-pulse ahop-stat-icon" aria-hidden="true"></i>

            </a>

        </div>

    @endcan



    @can('view', \App\Models\LabOrder::class)

        <div class="col-lg-2 col-md-4 col-sm-6 col-xs-6">

            <a href="{{ route('lab-orders.index', ['status' => 'ordered']) }}" class="ahop-stat-card ahop-stat-lab">

                <div class="ahop-stat-value" data-ahop-stat="lab_pending">{{ number_format($stats['lab_pending']) }}</div>

                <div class="ahop-stat-label">{{ trans('ahop.stat_lab_pending') }}</div>

                <i class="fas fa-flask ahop-stat-icon" aria-hidden="true"></i>

            </a>

        </div>

    @endcan



    @can('view', \App\Models\BillingInvoice::class)

        <div class="col-lg-2 col-md-4 col-sm-6 col-xs-6">

            <a href="{{ route('billing-invoices.index') }}" class="ahop-stat-card ahop-stat-billing">

                <div class="ahop-stat-value" data-ahop-stat="collections_today" data-ahop-stat-format="currency">₱{{ number_format($stats['collections_today'] ?? 0, 0) }}</div>

                <div class="ahop-stat-label">{{ trans('ahop.stat_collections_today') }}</div>

                <i class="fas fa-peso-sign ahop-stat-icon" aria-hidden="true"></i>

            </a>

        </div>

    @endcan



    @can('view', \App\Models\Asset::class)

        <div class="col-lg-2 col-md-4 col-sm-6 col-xs-6">

            <a href="{{ route('hardware.index') }}" class="ahop-stat-card ahop-stat-equipment">

                <div class="ahop-stat-value" data-ahop-stat="equipment">{{ number_format($stats['equipment']) }}</div>

                <div class="ahop-stat-label">{{ trans('ahop.stat_equipment') }}</div>

                <i class="fas fa-kit-medical ahop-stat-icon" aria-hidden="true"></i>

            </a>

        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 col-xs-6">

            <a href="{{ route('hardware.index', ['status_type' => 'Pending']) }}" class="ahop-stat-card ahop-stat-equipment-warn">

                <div class="ahop-stat-value" data-ahop-stat="equipment_pending">{{ number_format($stats['equipment_pending']) }}</div>

                <div class="ahop-stat-label">{{ trans('ahop.stat_equipment_repair') }}</div>

                <i class="fas fa-screwdriver-wrench ahop-stat-icon" aria-hidden="true"></i>

            </a>

        </div>

    @endcan

    @if (config('ahop.show_consumables') && \Gate::allows('view', \App\Models\Consumable::class))
        <div class="col-lg-2 col-md-4 col-sm-6 col-xs-6">
            <a href="{{ url('consumables') }}" class="ahop-stat-card ahop-stat-supplies{{ ($stats['supplies_low'] ?? 0) > 0 ? ' ahop-stat-supplies-warn' : '' }}">
                <div class="ahop-stat-value" data-ahop-stat="supplies_low">{{ number_format($stats['supplies_low'] ?? 0) }}</div>
                <div class="ahop-stat-label">{{ trans('ahop.stat_supplies_low') }}</div>
                <i class="fas fa-boxes-stacked ahop-stat-icon" aria-hidden="true"></i>
            </a>
        </div>
    @endif

</div>



<div class="row" style="margin-top: 8px;">

    <div class="col-md-12">

        <div class="box box-default ahop-panel ahop-dashboard-widget">

            <div class="box-header with-border">

                <h3 class="box-title">{{ trans('ahop.quick_actions') }}</h3>

            </div>

            <div class="box-body">

                <div class="ahop-quick-actions">

                    @can('create', \App\Models\Patient::class)

                        <a href="{{ route('patients.create') }}" class="ahop-quick-action">

                            <i class="fas fa-user-plus" aria-hidden="true"></i>

                            <span>{{ trans('admin/patients/table.create') }}</span>

                        </a>

                    @endcan

                    @can('create', \App\Models\Appointment::class)

                        <a href="{{ route('appointments.create') }}" class="ahop-quick-action">

                            <i class="fas fa-calendar-plus" aria-hidden="true"></i>

                            <span>{{ trans('admin/appointments/table.create') }}</span>

                        </a>

                    @endcan

                    @can('create', \App\Models\OpdVisit::class)

                        <a href="{{ route('opd-visits.create') }}" class="ahop-quick-action">

                            <i class="fas fa-stethoscope" aria-hidden="true"></i>

                            <span>{{ trans('admin/opd_visits/table.create') }}</span>

                        </a>

                    @endcan

                    @can('create', \App\Models\LabOrder::class)

                        <a href="{{ route('lab-orders.create') }}" class="ahop-quick-action">

                            <i class="fas fa-flask" aria-hidden="true"></i>

                            <span>{{ trans('admin/lab_orders/table.create') }}</span>

                        </a>

                    @endcan

                    @can('create', \App\Models\BillingInvoice::class)

                        <a href="{{ route('billing-invoices.create') }}" class="ahop-quick-action">

                            <i class="fas fa-file-invoice-dollar" aria-hidden="true"></i>

                            <span>{{ trans('admin/billing_invoices/table.create') }}</span>

                        </a>

                    @endcan

                    @can('create', \App\Models\Asset::class)

                        <a href="{{ route('hardware.create') }}" class="ahop-quick-action ahop-quick-action--muted">

                            <i class="fas fa-kit-medical" aria-hidden="true"></i>

                            <span>{{ trans('general.medical_equipment') }}</span>

                        </a>

                    @endcan

                </div>

            </div>

        </div>

    </div>

</div>



<div class="row ahop-dashboard-widgets">

    @can('view', \App\Models\Appointment::class)

        <div class="col-xs-12 col-md-4">

            <div class="box box-default ahop-panel ahop-dashboard-widget">

                <div class="box-header with-border">

                    <h3 class="box-title">{{ trans('ahop.recent_appointments_today') }}</h3>

                    <div class="box-tools pull-right">

                        <a href="{{ route('appointments.index') }}" class="btn btn-xs btn-default">{{ trans('general.viewall') }}</a>

                    </div>

                </div>

                <div class="box-body">

                    @if ($recentAppointments->count())

                        <ul class="ahop-activity-list" data-ahop-widget="recent-appointments">

                            @foreach ($recentAppointments as $appointment)

                                <li class="ahop-activity-list__item">

                                    <a href="{{ route('appointments.show', $appointment) }}" class="ahop-activity-list__link">

                                        <span class="ahop-activity-list__primary">{{ $appointment->appointment_number }}</span>

                                        <span class="ahop-activity-list__secondary">{{ $appointment->patient?->full_name }}</span>

                                    </a>

                                    <span class="ahop-badge ahop-badge-{{ $appointment->status }}">

                                        {{ $appointment->scheduled_at?->format('H:i') }}

                                    </span>

                                </li>

                            @endforeach

                        </ul>

                    @else

                        @include('partials.ahop-empty-state', [

                            'icon' => 'fa-calendar-day',

                            'title' => trans('ahop.empty_dashboard_appointments'),

                            'actionUrl' => auth()->user()->can('create', \App\Models\Appointment::class) ? route('appointments.create') : null,

                            'actionLabel' => auth()->user()->can('create', \App\Models\Appointment::class) ? trans('admin/appointments/table.create') : null,

                        ])

                    @endif

                </div>

            </div>

        </div>

    @endcan



    @can('view', \App\Models\OpdVisit::class)

        <div class="col-xs-12 col-md-4">

            <div class="box box-default ahop-panel ahop-dashboard-widget">

                <div class="box-header with-border">

                    <h3 class="box-title">{{ trans('ahop.recent_opd') }}</h3>

                    <div class="box-tools pull-right">

                        <a href="{{ route('opd-visits.index') }}" class="btn btn-xs btn-default">{{ trans('general.viewall') }}</a>

                    </div>

                </div>

                <div class="box-body">

                    @if ($recentOpd->count())

                        <ul class="ahop-activity-list" data-ahop-widget="recent-opd">

                            @foreach ($recentOpd as $visit)

                                <li class="ahop-activity-list__item">

                                    <a href="{{ route('opd-visits.show', $visit) }}" class="ahop-activity-list__link">

                                        <span class="ahop-activity-list__primary">{{ $visit->visit_number }}</span>

                                        <span class="ahop-activity-list__secondary">{{ $visit->patient?->full_name }}</span>

                                    </a>

                                    <span class="ahop-badge ahop-badge-{{ $visit->status }}">

                                        {{ \App\Models\OpdVisit::statusOptions()[$visit->status] ?? $visit->status }}

                                    </span>

                                </li>

                            @endforeach

                        </ul>

                    @else

                        @include('partials.ahop-empty-state', [

                            'icon' => 'fa-stethoscope',

                            'title' => trans('ahop.empty_dashboard_opd'),

                            'actionUrl' => auth()->user()->can('create', \App\Models\OpdVisit::class) ? route('opd-visits.create') : null,

                            'actionLabel' => auth()->user()->can('create', \App\Models\OpdVisit::class) ? trans('admin/opd_visits/table.create') : null,

                        ])

                    @endif

                </div>

            </div>

        </div>

    @endcan



    @can('view', \App\Models\LabOrder::class)

        <div class="col-xs-12 col-md-4">

            <div class="box box-default ahop-panel ahop-dashboard-widget">

                <div class="box-header with-border">

                    <h3 class="box-title">{{ trans('ahop.recent_lab') }}</h3>

                    <div class="box-tools pull-right">

                        <a href="{{ route('lab-orders.index') }}" class="btn btn-xs btn-default">{{ trans('general.viewall') }}</a>

                    </div>

                </div>

                <div class="box-body">

                    @if ($recentLab->count())

                        <ul class="ahop-activity-list" data-ahop-widget="recent-lab">

                            @foreach ($recentLab as $order)

                                <li class="ahop-activity-list__item">

                                    <a href="{{ route('lab-orders.show', $order) }}" class="ahop-activity-list__link">

                                        <span class="ahop-activity-list__primary">{{ $order->order_number }}</span>

                                        <span class="ahop-activity-list__secondary">{{ $order->patient?->full_name }}</span>

                                    </a>

                                    <span class="ahop-badge ahop-badge-{{ $order->status }}">

                                        {{ \App\Models\LabOrder::statusOptions()[$order->status] ?? $order->status }}

                                    </span>

                                </li>

                            @endforeach

                        </ul>

                    @else

                        @include('partials.ahop-empty-state', [

                            'icon' => 'fa-flask',

                            'title' => trans('ahop.empty_dashboard_lab'),

                            'actionUrl' => auth()->user()->can('create', \App\Models\LabOrder::class) ? route('lab-orders.create') : null,

                            'actionLabel' => auth()->user()->can('create', \App\Models\LabOrder::class) ? trans('admin/lab_orders/table.create') : null,

                        ])

                    @endif

                </div>

            </div>

        </div>

    @endcan

</div>



@can('view', \App\Models\Asset::class)

    <div class="row">

        <div class="col-md-12">

            <div class="box box-default ahop-panel ahop-dashboard-widget">

                <div class="box-header with-border">

                    <h3 class="box-title">{{ trans('ahop.equipment_by_status') }}</h3>

                    <div class="box-tools pull-right">

                        <a href="{{ route('hardware.index') }}" class="btn btn-xs btn-default">{{ trans('general.viewall') }}</a>

                    </div>

                </div>

                <div class="box-body">

                    @if ($equipmentByStatus->count())

                        <div class="row ahop-equipment-status-grid">

                            @foreach ($equipmentByStatus as $status)

                                <div class="col-xs-12 col-sm-6 col-md-3 ahop-equipment-status-col">

                                    <a href="{{ route('statuslabels.show', $status) }}" class="ahop-equipment-status-tile" data-status-id="{{ $status->id }}">

                                        <span class="ahop-equipment-status-dot" style="background-color: {{ $status->color ?: 'var(--ahop-primary)' }};"></span>

                                        <span class="ahop-equipment-status-name">{{ $status->name }}</span>

                                        <span class="ahop-equipment-status-count">{{ $status->assets_count }}</span>

                                    </a>

                                </div>

                            @endforeach

                        </div>

                    @else

                        @include('partials.ahop-empty-state', [

                            'icon' => 'fa-kit-medical',

                            'title' => trans('ahop.empty_dashboard_equipment'),

                            'message' => 'Run: php artisan ahop:seed-equipment --demo-assets',

                        ])

                    @endif

                </div>

            </div>

        </div>

    </div>

@endcan



@stop

@if (config('ahop.dashboard_auto_refresh.enabled'))
@section('moar_scripts')
<script>
(function () {
    var refreshUrl = @json(route('ahop.dashboard.data'));
    var intervalMs = {{ (int) config('ahop.dashboard_auto_refresh.interval_seconds', 60) * 1000 }};
    var metaEl = document.getElementById('ahop-dashboard-refresh-meta');
    var timerId = null;

    function formatStatValue(el, value) {
        var format = el.getAttribute('data-ahop-stat-format');
        var number = Number(value || 0);
        if (format === 'currency') {
            el.textContent = '₱' + number.toLocaleString(undefined, { maximumFractionDigits: 0 });
            return;
        }
        el.textContent = number.toLocaleString(undefined, { maximumFractionDigits: 0 });
    }

    function renderActivityList(container, items) {
        if (!container || !items || !items.length) {
            return;
        }
        container.innerHTML = '';
        items.forEach(function (item) {
            var li = document.createElement('li');
            li.className = 'ahop-activity-list__item';
            li.innerHTML =
                '<a href="' + item.url + '" class="ahop-activity-list__link">' +
                    '<span class="ahop-activity-list__primary"></span>' +
                    '<span class="ahop-activity-list__secondary"></span>' +
                '</a>' +
                '<span class="ahop-badge ahop-badge-' + item.badge_class + '"></span>';
            li.querySelector('.ahop-activity-list__primary').textContent = item.primary || '';
            li.querySelector('.ahop-activity-list__secondary').textContent = item.secondary || '';
            li.querySelector('.ahop-badge').textContent = item.badge || '';
            container.appendChild(li);
        });
    }

    function applyPayload(data) {
        if (data.stats) {
            document.querySelectorAll('[data-ahop-stat]').forEach(function (el) {
                var key = el.getAttribute('data-ahop-stat');
                if (Object.prototype.hasOwnProperty.call(data.stats, key)) {
                    formatStatValue(el, data.stats[key]);
                }
            });

            var suppliesValue = document.querySelector('[data-ahop-stat="supplies_low"]');
            if (suppliesValue) {
                var suppliesCard = suppliesValue.closest('.ahop-stat-card');
                if (suppliesCard) {
                    suppliesCard.classList.toggle('ahop-stat-supplies-warn', Number(data.stats.supplies_low || 0) > 0);
                }
            }
        }

        renderActivityList(document.querySelector('[data-ahop-widget="recent-appointments"]'), data.recent_appointments);
        renderActivityList(document.querySelector('[data-ahop-widget="recent-opd"]'), data.recent_opd);
        renderActivityList(document.querySelector('[data-ahop-widget="recent-lab"]'), data.recent_lab);

        if (data.equipment_by_status) {
            data.equipment_by_status.forEach(function (row) {
                var tile = document.querySelector('[data-status-id="' + row.id + '"] .ahop-equipment-status-count');
                if (tile) {
                    tile.textContent = Number(row.count || 0).toLocaleString(undefined, { maximumFractionDigits: 0 });
                }
            });
        }

        if (metaEl && data.refreshed_at) {
            var refreshed = new Date(data.refreshed_at);
            metaEl.textContent = ' · {{ trans('ahop.dashboard_refreshed_at') }} ' +
                refreshed.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
        }
    }

    function refreshDashboard() {
        if (document.hidden) {
            return;
        }
        fetch(refreshUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('refresh failed');
                }
                return response.json();
            })
            .then(applyPayload)
            .catch(function () {
                if (metaEl) {
                    metaEl.textContent = ' · {{ trans('ahop.dashboard_refresh_failed') }}';
                }
            });
    }

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            refreshDashboard();
        }
    });

    timerId = window.setInterval(refreshDashboard, intervalMs);
    window.addEventListener('beforeunload', function () {
        if (timerId) {
            window.clearInterval(timerId);
        }
    });
})();
</script>
@stop
@endif

