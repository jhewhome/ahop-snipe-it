@extends('layouts/edit-form', [
    'createText' => trans('admin/lab_orders/table.create'),
    'updateText' => trans('admin/lab_orders/table.update'),
    'formAction' => (isset($item->id)) ? route('lab-orders.update', ['lab_order' => $item->id]) : route('lab-orders.store'),
    'boxClasses' => 'ahop-panel',
])

@section('inputFields')

    <div class="form-group{{ $errors->has('order_number') ? ' has-error' : '' }}">
        <label for="order_number" class="col-md-3 control-label">{{ trans('admin/lab_orders/table.order_number') }}</label>
        <div class="col-md-8">
            <input class="form-control" type="text" name="order_number" id="order_number" value="{{ old('order_number', $item->order_number) }}" required />
            {!! $errors->first('order_number', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <div class="form-group{{ $errors->has('patient_id') ? ' has-error' : '' }}">
        <label for="patient_id" class="col-md-3 control-label">{{ trans('admin/lab_orders/table.patient') }}</label>
        <div class="col-md-8">
            <select name="patient_id" id="patient_id" class="form-control" required>
                <option value="">{{ trans('general.select') }}...</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->id }}" {{ (int) old('patient_id', $item->patient_id) === $patient->id ? 'selected' : '' }}>
                        {{ $patient->patient_number }} — {{ $patient->full_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($item->opd_visit_id)
        <input type="hidden" name="opd_visit_id" value="{{ old('opd_visit_id', $item->opd_visit_id) }}">
        <div class="form-group">
            <label class="col-md-3 control-label">{{ trans('admin/lab_orders/table.opd_visit') }}</label>
            <div class="col-md-8">
                <p class="form-control-static">
                    @if ($item->opdVisit)
                        <a href="{{ route('opd-visits.show', $item->opdVisit) }}">{{ $item->opdVisit->visit_number }}</a>
                    @else
                        #{{ $item->opd_visit_id }}
                    @endif
                </p>
            </div>
        </div>
    @endif

    <div class="form-group{{ $errors->has('test_panel') ? ' has-error' : '' }}">
        <label for="test_panel" class="col-md-3 control-label">{{ trans('admin/lab_orders/table.test_panel') }}</label>
        <div class="col-md-8">
            <select name="test_panel" id="test_panel" class="form-control" required>
                @foreach (\App\Models\LabOrder::testPanelOptions() as $code => $label)
                    <option value="{{ $code }}" {{ old('test_panel', $item->test_panel) === $code ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="priority" class="col-md-3 control-label">{{ trans('admin/lab_orders/table.priority') }}</label>
        <div class="col-md-8">
            <select name="priority" id="priority" class="form-control" required>
                @foreach (\App\Models\LabOrder::priorityOptions() as $value => $label)
                    <option value="{{ $value }}" {{ old('priority', $item->priority) === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="status" class="col-md-3 control-label">{{ trans('admin/lab_orders/table.status') }}</label>
        <div class="col-md-8">
            <select name="status" id="status" class="form-control" required>
                @foreach (\App\Models\LabOrder::statusOptions() as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $item->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="ordered_at" class="col-md-3 control-label">{{ trans('admin/lab_orders/table.ordered_at') }}</label>
        <div class="col-md-8">
            <input class="form-control" type="datetime-local" name="ordered_at" id="ordered_at"
                   value="{{ old('ordered_at', optional($item->ordered_at)->format('Y-m-d\TH:i')) }}" required />
        </div>
    </div>

    @include ('partials.forms.edit.user-select', [
        'translated_name' => trans('admin/lab_orders/table.ordered_by'),
        'fieldname' => 'ordered_by',
    ])

    <div class="form-group">
        <label for="clinical_notes" class="col-md-3 control-label">{{ trans('admin/lab_orders/table.clinical_notes') }}</label>
        <div class="col-md-8">
            <textarea class="form-control" name="clinical_notes" id="clinical_notes" rows="3">{{ old('clinical_notes', $item->clinical_notes) }}</textarea>
        </div>
    </div>

    @if (\App\Models\Company::canManageUsersCompanies())
        @include ('partials.forms.edit.company-select', ['translated_name' => trans('general.company'), 'fieldname' => 'company_id'])
    @endif

@stop
