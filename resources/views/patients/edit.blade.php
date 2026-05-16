@extends('layouts/edit-form', [
    'createText' => trans('admin/patients/table.create'),
    'updateText' => trans('admin/patients/table.update'),
    'formAction' => (isset($item->id)) ? route('patients.update', ['patient' => $item->id]) : route('patients.store'),
])

@section('inputFields')

    <div class="form-group{{ $errors->has('bhc_id') ? ' has-error' : '' }}">
        <label for="bhc_id" class="col-md-3 control-label">{{ trans('admin/patients/table.bhc_id') }}</label>
        <div class="col-md-8">
            <input class="form-control" type="text" name="bhc_id" id="bhc_id" value="{{ old('bhc_id', $item->bhc_id) }}" required />
            {!! $errors->first('bhc_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
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

    @if (\App\Models\Company::canManageUsersCompanies())
        @include ('partials.forms.edit.company-select', ['translated_name' => trans('general.company'), 'fieldname' => 'company_id'])
    @endif

    <div class="form-group{{ $errors->has('notes') ? ' has-error' : '' }}">
        <label for="notes" class="col-md-3 control-label">{{ trans('admin/patients/table.notes') }}</label>
        <div class="col-md-8">
            <textarea class="form-control" name="notes" id="notes" rows="4">{{ old('notes', $item->notes) }}</textarea>
            {!! $errors->first('notes', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

@stop
