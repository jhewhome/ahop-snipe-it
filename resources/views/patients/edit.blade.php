@extends('layouts/edit-form', [
    'createText' => trans('admin/patients/table.create'),
    'updateText' => trans('admin/patients/table.update'),
    'formAction' => (isset($item->id)) ? route('patients.update', ['patient' => $item->id]) : route('patients.store'),
    'boxClasses' => 'ahop-panel ahop-patients-panel',
])

@section('inputFields')

    <div class="form-group{{ $errors->has('patient_number') ? ' has-error' : '' }}">
        <label for="patient_number" class="col-md-3 control-label">{{ trans('admin/patients/table.patient_number') }}</label>
        <div class="col-md-8">
            <input class="form-control" type="text" name="patient_number" id="patient_number" value="{{ old('patient_number', $item->patient_number) }}" required />
            {!! $errors->first('patient_number', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <div class="form-group{{ $errors->has('full_name') ? ' has-error' : '' }}">
        <label for="full_name" class="col-md-3 control-label">{{ trans('admin/patients/table.full_name') }}</label>
        <div class="col-md-8">
            <input class="form-control" type="text" name="full_name" id="full_name" value="{{ old('full_name', $item->full_name) }}" required />
            {!! $errors->first('full_name', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <div class="form-group{{ $errors->has('sex') ? ' has-error' : '' }}">
        <label for="sex" class="col-md-3 control-label">{{ trans('admin/patients/table.sex') }}</label>
        <div class="col-md-8">
            <select name="sex" id="sex" class="form-control" required>
                <option value="">{{ trans('general.select') }}...</option>
                <option value="M" {{ old('sex', $item->sex) === 'M' ? 'selected' : '' }}>Male</option>
                <option value="F" {{ old('sex', $item->sex) === 'F' ? 'selected' : '' }}>Female</option>
            </select>
            {!! $errors->first('sex', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <div class="form-group{{ $errors->has('birthdate') ? ' has-error' : '' }}">
        <label for="birthdate" class="col-md-3 control-label">{{ trans('admin/patients/table.birthdate') }}</label>
        <div class="col-md-8">
            <input class="form-control" type="date" name="birthdate" id="birthdate" value="{{ old('birthdate', optional($item->birthdate)->format('Y-m-d')) }}" required />
            {!! $errors->first('birthdate', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <div class="form-group{{ $errors->has('contact_number') ? ' has-error' : '' }}">
        <label for="contact_number" class="col-md-3 control-label">{{ trans('admin/patients/table.contact_number') }}</label>
        <div class="col-md-8">
            <input class="form-control" type="text" name="contact_number" id="contact_number" value="{{ old('contact_number', $item->contact_number) }}" />
            {!! $errors->first('contact_number', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
        <label for="email" class="col-md-3 control-label">{{ trans('admin/patients/table.email') }}</label>
        <div class="col-md-8">
            <input class="form-control" type="email" name="email" id="email" value="{{ old('email', $item->email) }}" />
            {!! $errors->first('email', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <div class="form-group{{ $errors->has('allergies') ? ' has-error' : '' }}">
        <label for="allergies" class="col-md-3 control-label">{{ trans('admin/patients/table.allergies') }}</label>
        <div class="col-md-8">
            <textarea class="form-control" name="allergies" id="allergies" rows="2" placeholder="e.g. Penicillin — rash">{{ old('allergies', $item->allergies) }}</textarea>
            {!! $errors->first('allergies', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <div class="form-group{{ $errors->has('problem_list') ? ' has-error' : '' }}">
        <label for="problem_list" class="col-md-3 control-label">{{ trans('admin/patients/table.problem_list') }}</label>
        <div class="col-md-8">
            <textarea class="form-control" name="problem_list" id="problem_list" rows="3" placeholder="e.g. Hypertension, Type 2 diabetes">{{ old('problem_list', $item->problem_list) }}</textarea>
            {!! $errors->first('problem_list', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    @if (\App\Models\Company::canManageUsersCompanies() || $item->company_id)
        @include('partials.ahop-clinic-site-field', [
            'item' => $item,
            'help_text' => trans('admin/patients/table.clinic_site_help'),
        ])
    @endif

    <div class="form-group{{ $errors->has('notes') ? ' has-error' : '' }}">
        <label for="notes" class="col-md-3 control-label">{{ trans('admin/patients/table.notes') }}</label>
        <div class="col-md-8">
            <textarea class="form-control" name="notes" id="notes" rows="4">{{ old('notes', $item->notes) }}</textarea>
            {!! $errors->first('notes', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

@stop
