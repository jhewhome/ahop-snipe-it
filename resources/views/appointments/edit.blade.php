@extends('layouts/edit-form', [
    'createText' => trans('admin/appointments/table.create'),
    'updateText' => trans('admin/appointments/table.update'),
    'formAction' => (isset($item->id)) ? route('appointments.update', ['appointment' => $item->id]) : route('appointments.store'),
    'boxClasses' => 'ahop-panel ahop-appointments-panel',
])

@section('inputFields')

    <div class="form-group{{ $errors->has('appointment_number') ? ' has-error' : '' }}">
        <label for="appointment_number" class="col-md-3 control-label">{{ trans('admin/appointments/table.appointment_number') }}</label>
        <div class="col-md-8">
            <input class="form-control" type="text" name="appointment_number" id="appointment_number" value="{{ old('appointment_number', $item->appointment_number) }}" required />
            {!! $errors->first('appointment_number', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times"></i> :message</span>') !!}
        </div>
    </div>

    <div class="form-group{{ $errors->has('patient_id') ? ' has-error' : '' }}">
        <label for="patient_id" class="col-md-3 control-label">{{ trans('admin/appointments/table.patient') }}</label>
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

    @include('partials.forms.edit.physician-select', [
        'translated_name' => trans('admin/appointments/table.physician'),
        'fieldname' => 'physician_id',
        'item' => $item,
        'physicians' => $physicians ?? collect(),
        'placeholder' => trans('admin/reception/table.physician_placeholder'),
    ])

    <div class="form-group{{ $errors->has('scheduled_at') ? ' has-error' : '' }}">
        <label for="scheduled_at" class="col-md-3 control-label">{{ trans('admin/appointments/table.scheduled_at') }}</label>
        <div class="col-md-8">
            <input class="form-control" type="datetime-local" name="scheduled_at" id="scheduled_at"
                   value="{{ old('scheduled_at', optional($item->scheduled_at)->format('Y-m-d\TH:i')) }}" required />
        </div>
    </div>

    <div class="form-group{{ $errors->has('duration_minutes') ? ' has-error' : '' }}">
        <label for="duration_minutes" class="col-md-3 control-label">{{ trans('admin/appointments/table.duration_minutes') }}</label>
        <div class="col-md-8">
            <input class="form-control" type="number" name="duration_minutes" id="duration_minutes" min="5" max="480" step="5"
                   value="{{ old('duration_minutes', $item->duration_minutes ?? 30) }}" required />
        </div>
    </div>

    <div class="form-group{{ $errors->has('visit_type') ? ' has-error' : '' }}">
        <label for="visit_type" class="col-md-3 control-label">{{ trans('admin/appointments/table.visit_type') }}</label>
        <div class="col-md-8">
            <select name="visit_type" id="visit_type" class="form-control" required>
                @foreach (\App\Models\Appointment::visitTypeOptions() as $value => $label)
                    <option value="{{ $value }}" {{ old('visit_type', $item->visit_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
        <label for="status" class="col-md-3 control-label">{{ trans('admin/appointments/table.status') }}</label>
        <div class="col-md-8">
            @php
                $statusOptions = $item->exists
                    ? $item->editableStatusOptions()
                    : \App\Models\Appointment::creatableStatusOptions();
            @endphp
            @if (count($statusOptions) === 1)
                @php($onlyStatus = array_key_first($statusOptions))
                <input type="hidden" name="status" value="{{ old('status', $onlyStatus) }}">
                <p class="form-control-static" id="status">
                    <span class="ahop-badge ahop-badge-{{ $onlyStatus }}">{{ $statusOptions[$onlyStatus] }}</span>
                </p>
                <p class="help-block"><small>{{ trans('admin/appointments/table.status_help_schedule') }}</small></p>
            @else
                <select name="status" id="status" class="form-control" required>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $item->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="help-block"><small>{{ trans('admin/appointments/table.status_help_edit') }}</small></p>
            @endif
        </div>
    </div>

    <div class="form-group{{ $errors->has('reason') ? ' has-error' : '' }}">
        <label for="reason" class="col-md-3 control-label">{{ trans('admin/appointments/table.reason') }}</label>
        <div class="col-md-8">
            <textarea class="form-control" name="reason" id="reason" rows="3">{{ old('reason', $item->reason) }}</textarea>
        </div>
    </div>

    <div class="form-group{{ $errors->has('notes') ? ' has-error' : '' }}">
        <label for="notes" class="col-md-3 control-label">{{ trans('admin/appointments/table.notes') }}</label>
        <div class="col-md-8">
            <textarea class="form-control" name="notes" id="notes" rows="2">{{ old('notes', $item->notes) }}</textarea>
        </div>
    </div>

@stop
