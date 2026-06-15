@extends('layouts/default')

@section('title')
    {{ trans('general.lab_orders') }}
    @parent
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default ahop-panel">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('general.lab_orders') }}</h2>
                    <div class="box-tools pull-right">
                        @can('create', \App\Models\LabOrder::class)
                            <a href="{{ route('lab-orders.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus" aria-hidden="true"></i> {{ trans('admin/lab_orders/table.create') }}
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="box-body">
                    <form method="get" action="{{ route('lab-orders.index') }}" class="form-inline" style="margin-bottom: 15px;">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control" placeholder="{{ trans('general.search') }}"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="form-group" style="margin-left: 8px;">
                            <select name="status" class="form-control">
                                <option value="">{{ trans('admin/lab_orders/table.status') }} — {{ trans('general.all') }}</option>
                                @foreach (\App\Models\LabOrder::statusOptions() as $value => $label)
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
                                <th>{{ trans('admin/lab_orders/table.order_number') }}</th>
                                <th>{{ trans('admin/lab_orders/table.patient') }}</th>
                                <th>{{ trans('admin/lab_orders/table.test_panel') }}</th>
                                <th>{{ trans('admin/lab_orders/table.status') }}</th>
                                <th>{{ trans('admin/lab_orders/table.priority') }}</th>
                                <th>{{ trans('admin/lab_orders/table.ordered_at') }}</th>
                                <th>{{ trans('admin/lab_orders/table.results_count') }}</th>
                                <th class="text-right">{{ trans('table.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($labOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('lab-orders.show', $order) }}">{{ $order->order_number }}</a>
                                    </td>
                                    <td>
                                        @if ($order->patient)
                                            <a href="{{ route('patients.show', $order->patient) }}">{{ $order->patient->full_name }}</a>
                                            <br><small class="text-muted">{{ $order->patient->patient_number }}</small>
                                        @endif
                                    </td>
                                    <td>{{ \App\Models\LabOrder::testPanelOptions()[$order->test_panel] ?? $order->test_panel }}</td>
                                    <td><span class="ahop-badge ahop-badge-{{ $order->status }}">{{ \App\Models\LabOrder::statusOptions()[$order->status] ?? $order->status }}</span></td>
                                    <td>
                                        @if ($order->priority === 'urgent')
                                            <span class="ahop-badge ahop-badge-urgent">{{ \App\Models\LabOrder::priorityOptions()[$order->priority] ?? $order->priority }}</span>
                                        @else
                                            {{ \App\Models\LabOrder::priorityOptions()[$order->priority] ?? $order->priority }}
                                        @endif
                                    </td>
                                    <td>{{ $order->ordered_at?->format('Y-m-d H:i') }}</td>
                                    <td>{{ $order->results_count }}</td>
                                    <td class="text-right">
                                        @can('view', $order)
                                            <a href="{{ route('lab-orders.show', $order) }}" class="btn btn-sm btn-default" title="{{ trans('general.view') }}">
                                                <i class="fas fa-eye" aria-hidden="true"></i>
                                            </a>
                                        @endcan
                                        @can('update', $order)
                                            <a href="{{ route('lab-orders.edit', $order) }}" class="btn btn-sm btn-warning" title="{{ trans('general.edit') }}">
                                                <i class="fas fa-pencil" aria-hidden="true"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr class="ahop-empty-row">
                                    <td colspan="8">
                                        @include('partials.ahop-empty-state', [
                                            'icon' => 'fa-flask',
                                            'title' => trans('ahop.empty_lab_title'),
                                            'message' => trans('ahop.empty_lab_message'),
                                            'actionUrl' => auth()->user()->can('create', \App\Models\LabOrder::class) ? route('lab-orders.create') : null,
                                            'actionLabel' => auth()->user()->can('create', \App\Models\LabOrder::class) ? trans('admin/lab_orders/table.create') : null,
                                            'compact' => true,
                                        ])
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $labOrders->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
