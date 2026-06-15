@extends('layouts/default')

@section('title')
    {{ $order->display_name }}
    @parent
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default ahop-panel ahop-clinical-detail-panel">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('admin/lab_orders/table.order_number') }}: {{ $order->order_number }}</h2>
                    <div class="box-tools pull-right">
                        @can('update', $order)
                            <a href="{{ route('lab-orders.edit', $order) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-pencil" aria-hidden="true"></i> {{ trans('general.edit') }}
                            </a>
                        @endcan
                        @can('delete', $order)
                            <form method="post" action="{{ route('lab-orders.destroy', $order) }}" style="display:inline;" onsubmit="return confirm('{{ trans('admin/lab_orders/message.delete.confirm') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash" aria-hidden="true"></i> {{ trans('general.delete') }}
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>

                <div class="box-body">
                    <table class="table">
                        <tbody>
                        <tr>
                            <th style="width: 30%;">{{ trans('admin/lab_orders/table.patient') }}</th>
                            <td>
                                @if ($order->patient)
                                    <a href="{{ route('patients.show', $order->patient) }}">
                                        {{ $order->patient->full_name }} ({{ $order->patient->patient_number }})
                                    </a>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/lab_orders/table.test_panel') }}</th>
                            <td>{{ \App\Models\LabOrder::testPanelOptions()[$order->test_panel] ?? $order->test_panel }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/lab_orders/table.status') }}</th>
                            <td>{{ \App\Models\LabOrder::statusOptions()[$order->status] ?? $order->status }}</td>
                        </tr>
                        @if ($order->opdVisit)
                            <tr>
                                <th>{{ trans('admin/lab_orders/table.opd_visit') }}</th>
                                <td>
                                    <a href="{{ route('opd-visits.show', $order->opdVisit) }}">{{ $order->opdVisit->visit_number }}</a>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <th>{{ trans('admin/lab_orders/table.priority') }}</th>
                            <td>{{ \App\Models\LabOrder::priorityOptions()[$order->priority] ?? $order->priority }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/lab_orders/table.ordered_at') }}</th>
                            <td>{{ $order->ordered_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                        @if ($order->completed_at)
                            <tr>
                                <th>{{ trans('admin/lab_orders/table.completed_at') }}</th>
                                <td>{{ $order->completed_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endif
                        @if ($order->clinical_notes)
                            <tr>
                                <th>{{ trans('admin/lab_orders/table.clinical_notes') }}</th>
                                <td>{{ $order->clinical_notes }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>

                    <hr>
                    <h4 class="ahop-section-title">{{ trans('admin/lab_orders/table.results') }}</h4>

                    @if ($order->results->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ trans('admin/lab_orders/table.test_name') }}</th>
                                    <th>{{ trans('admin/lab_orders/table.result_value') }}</th>
                                    <th>{{ trans('admin/lab_orders/table.unit') }}</th>
                                    <th>{{ trans('admin/lab_orders/table.reference_range') }}</th>
                                    <th>{{ trans('admin/lab_orders/table.flag') }}</th>
                                    <th>{{ trans('admin/lab_orders/table.result_at') }}</th>
                                    @can('update', $order)
                                        <th></th>
                                    @endcan
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($order->results as $result)
                                    <tr class="{{ $result->flag === 'critical' ? 'danger' : ($result->flag === 'high' || $result->flag === 'low' ? 'warning' : '') }}">
                                        <td>{{ $result->test_name }}@if($result->test_code) <small class="text-muted">({{ $result->test_code }})</small>@endif</td>
                                        <td><strong>{{ $result->result_value }}</strong></td>
                                        <td>{{ $result->unit }}</td>
                                        <td>{{ $result->reference_range }}</td>
                                        <td>{{ $result->flag ? (\App\Models\LabResult::flagOptions()[$result->flag] ?? $result->flag) : '—' }}</td>
                                        <td>{{ $result->result_at?->format('Y-m-d H:i') }}</td>
                                        @can('update', $order)
                                            <td>
                                                <form method="post" action="{{ route('lab-orders.results.destroy', ['lab_order' => $order, 'result' => $result]) }}" onsubmit="return confirm('{{ trans('general.delete') }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">{{ trans('admin/lab_orders/table.no_results') }}</p>
                    @endif

                    @can('update', $order)
                        <hr>
                        <h4 class="ahop-section-title">{{ trans('admin/lab_orders/table.add_result') }}</h4>
                        <form method="post" action="{{ route('lab-orders.results.store', $order) }}" class="form-horizontal">
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <input class="form-control" name="test_code" placeholder="{{ trans('admin/lab_orders/table.test_code') }}" value="{{ old('test_code') }}">
                                </div>
                                <div class="col-md-3">
                                    <input class="form-control" name="test_name" placeholder="{{ trans('admin/lab_orders/table.test_name') }} *" value="{{ old('test_name') }}" required>
                                </div>
                                <div class="col-md-2">
                                    <input class="form-control" name="result_value" placeholder="{{ trans('admin/lab_orders/table.result_value') }} *" value="{{ old('result_value') }}" required>
                                </div>
                                <div class="col-md-1">
                                    <input class="form-control" name="unit" placeholder="{{ trans('admin/lab_orders/table.unit') }}" value="{{ old('unit') }}">
                                </div>
                                <div class="col-md-2">
                                    <select name="flag" class="form-control">
                                        <option value="">{{ trans('admin/lab_orders/table.flag') }}</option>
                                        @foreach (\App\Models\LabResult::flagOptions() as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary btn-block">{{ trans('general.save') }}</button>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 8px;">
                                <div class="col-md-4">
                                    <input class="form-control" name="reference_range" placeholder="{{ trans('admin/lab_orders/table.reference_range') }}" value="{{ old('reference_range') }}">
                                </div>
                            </div>
                        </form>
                    @endcan

                    @can('view', $order)
                        <hr>
                        <h4 class="ahop-section-title">{{ trans('admin/lab_orders/table.lis_integration') }}</h4>
                        <p class="text-muted">{{ trans('admin/lab_orders/table.api_endpoint_hint') }}</p>
                        <pre class="well" style="font-size: 12px;">POST {{ url('/api/v1/lab/orders/'.$order->id.'/results') }}
Authorization: Bearer &lt;your_api_token&gt;
Content-Type: application/json</pre>
                    @endcan
                </div>

                <div class="box-footer text-right">
                    <a href="{{ route('lab-orders.index') }}" class="btn btn-default">{{ trans('general.back') }}</a>
                </div>
            </div>
        </div>
    </div>
@stop
