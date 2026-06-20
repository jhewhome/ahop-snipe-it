@extends('layouts/edit-form', [

    'createText' => trans('admin/opd_visits/table.create'),

    'updateText' => trans('admin/opd_visits/table.update'),

    'formAction' => (isset($item->id)) ? route('opd-visits.update', ['opd_visit' => $item->id]) : route('opd-visits.store'),

    'boxClasses' => 'ahop-panel',

])



@section('inputFields')



    <div class="nav-tabs-custom ahop-clinical-analytics-tabs ahop-opd-visit-form-tabs">

        <ul class="nav nav-tabs" role="tablist">

            <li class="active" role="presentation">

                <a href="#opd-tab-visit" data-toggle="tab" role="tab" aria-controls="opd-tab-visit" aria-selected="true">

                    <i class="fas fa-clipboard-list" aria-hidden="true"></i> {{ trans('admin/opd_visits/table.tab_visit_details') }}

                </a>

            </li>

            <li role="presentation">

                <a href="#opd-tab-vitals" data-toggle="tab" role="tab" aria-controls="opd-tab-vitals" aria-selected="false">

                    <i class="fas fa-heartbeat" aria-hidden="true"></i> {{ trans('admin/opd_visits/table.vitals') }}

                </a>

            </li>

            <li role="presentation">

                <a href="#opd-tab-med-cert" data-toggle="tab" role="tab" aria-controls="opd-tab-med-cert" aria-selected="false">

                    <i class="fas fa-file-medical-alt" aria-hidden="true"></i> {{ trans('admin/opd_visits/med_cert.med_cert_section') }}

                </a>

            </li>

        </ul>



        <div class="tab-content ahop-opd-visit-form-tab-content">



            <div class="tab-pane active" id="opd-tab-visit" role="tabpanel">



                <div class="form-group{{ $errors->has('visit_number') ? ' has-error' : '' }}">

                    <label for="visit_number" class="col-md-3 control-label">{{ trans('admin/opd_visits/table.visit_number') }}</label>

                    <div class="col-md-8">

                        <input class="form-control" type="text" name="visit_number" id="visit_number" value="{{ old('visit_number', $item->visit_number) }}" required />

                        {!! $errors->first('visit_number', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}

                    </div>

                </div>



                <div class="form-group{{ $errors->has('patient_id') ? ' has-error' : '' }}">

                    <label for="patient_id" class="col-md-3 control-label">{{ trans('admin/opd_visits/table.patient') }}</label>

                    <div class="col-md-8">

                        <select name="patient_id" id="patient_id" class="form-control" required>

                            <option value="">{{ trans('general.select') }}...</option>

                            @foreach ($patients as $patient)

                                <option value="{{ $patient->id }}" {{ (int) old('patient_id', $item->patient_id) === $patient->id ? 'selected' : '' }}>

                                    {{ $patient->patient_number }} — {{ $patient->full_name }}

                                </option>

                            @endforeach

                        </select>

                        {!! $errors->first('patient_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}

                    </div>

                </div>



                @include('partials.ahop-opd-patient-clinical-panel', ['patientClinicalMap' => $patientClinicalMap ?? []])



                @include('partials.forms.edit.physician-select', [

                    'translated_name' => trans('admin/opd_visits/table.physician'),

                    'fieldname' => 'physician_id',

                    'item' => $item,

                    'physicians' => $physicians ?? collect(),

                    'placeholder' => trans('admin/opd_visits/table.physician_placeholder'),

                    'help_text' => trans('admin/opd_visits/table.physician_help'),

                ])



                <div class="form-group{{ $errors->has('visit_date') ? ' has-error' : '' }}">

                    <label for="visit_date" class="col-md-3 control-label">{{ trans('admin/opd_visits/table.visit_date') }}</label>

                    <div class="col-md-8">

                        <input class="form-control" type="datetime-local" name="visit_date" id="visit_date"

                               value="{{ old('visit_date', optional($item->visit_date)->format('Y-m-d\TH:i')) }}" required />

                        {!! $errors->first('visit_date', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}

                    </div>

                </div>



                <div class="form-group{{ $errors->has('visit_type') ? ' has-error' : '' }}">

                    <label for="visit_type" class="col-md-3 control-label">{{ trans('admin/opd_visits/table.visit_type') }}</label>

                    <div class="col-md-8">

                        <select name="visit_type" id="visit_type" class="form-control" required>

                            @foreach (\App\Models\OpdVisit::visitTypeOptions() as $value => $label)

                                <option value="{{ $value }}" {{ old('visit_type', $item->visit_type) === $value ? 'selected' : '' }}>{{ $label }}</option>

                            @endforeach

                        </select>

                    </div>

                </div>



                <div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">

                    <label for="status" class="col-md-3 control-label">{{ trans('admin/opd_visits/table.status') }}</label>

                    <div class="col-md-8">

                        <select name="status" id="status" class="form-control" required>

                            @foreach (\App\Models\OpdVisit::statusOptions() as $value => $label)

                                <option value="{{ $value }}" {{ old('status', $item->status) === $value ? 'selected' : '' }}>{{ $label }}</option>

                            @endforeach

                        </select>

                    </div>

                </div>



                <div class="form-group{{ $errors->has('chief_complaint') ? ' has-error' : '' }}">

                    <label for="chief_complaint" class="col-md-3 control-label">{{ trans('admin/opd_visits/table.chief_complaint') }}</label>

                    <div class="col-md-8">

                        <textarea class="form-control" name="chief_complaint" id="chief_complaint" rows="3">{{ old('chief_complaint', $item->chief_complaint) }}</textarea>

                    </div>

                </div>



                <div class="form-group">

                    <label for="assessment" class="col-md-3 control-label">{{ trans('admin/opd_visits/table.assessment') }}</label>

                    <div class="col-md-8">

                        <textarea class="form-control" name="assessment" id="assessment" rows="4">{{ old('assessment', $item->assessment) }}</textarea>

                    </div>

                </div>



                <div class="form-group">

                    <label for="diagnosis" class="col-md-3 control-label">{{ trans('admin/opd_visits/table.diagnosis') }}</label>

                    <div class="col-md-8">

                        <textarea class="form-control" name="diagnosis" id="diagnosis" rows="3">{{ old('diagnosis', $item->diagnosis) }}</textarea>

                    </div>

                </div>



                @if (\App\Models\Company::canManageUsersCompanies() || $item->company_id)

                    @include('partials.ahop-clinic-site-field', [

                        'item' => $item,

                        'help_text' => trans('admin/opd_visits/table.clinic_site_intake_help'),

                    ])

                @endif



            </div>



            <div class="tab-pane" id="opd-tab-vitals" role="tabpanel">



                <p class="col-md-offset-3 col-md-8 help-block ahop-opd-tab-intro">{{ trans('admin/opd_visits/table.vitals_tab_help') }}</p>



                <div class="form-group{{ $errors->has('blood_pressure') ? ' has-error' : '' }}">

                    <label for="blood_pressure" class="col-md-3 control-label">{{ trans('admin/opd_visits/table.blood_pressure') }}</label>

                    <div class="col-md-8">

                        <input class="form-control" type="text" name="blood_pressure" id="blood_pressure" placeholder="120/80"

                               value="{{ old('blood_pressure', $item->blood_pressure) }}" />

                        {!! $errors->first('blood_pressure', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}

                    </div>

                </div>

                <div class="form-group{{ $errors->has('pulse_rate') ? ' has-error' : '' }}">

                    <label for="pulse_rate" class="col-md-3 control-label">{{ trans('admin/opd_visits/table.pulse_rate') }}</label>

                    <div class="col-md-8">

                        <input class="form-control" type="number" name="pulse_rate" id="pulse_rate" min="0" max="300"

                               value="{{ old('pulse_rate', $item->pulse_rate) }}" />

                        {!! $errors->first('pulse_rate', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}

                    </div>

                </div>

                <div class="form-group{{ $errors->has('temperature') ? ' has-error' : '' }}">

                    <label for="temperature" class="col-md-3 control-label">{{ trans('admin/opd_visits/table.temperature') }}</label>

                    <div class="col-md-8">

                        <input class="form-control" type="number" step="0.1" name="temperature" id="temperature"

                               value="{{ old('temperature', $item->temperature) }}" />

                        {!! $errors->first('temperature', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}

                    </div>

                </div>

                <div class="form-group{{ $errors->has('weight_kg') ? ' has-error' : '' }}">

                    <label for="weight_kg" class="col-md-3 control-label">{{ trans('admin/opd_visits/table.weight_kg') }}</label>

                    <div class="col-md-8">

                        <input class="form-control" type="number" step="0.01" name="weight_kg" id="weight_kg"

                               value="{{ old('weight_kg', $item->weight_kg) }}" />

                        {!! $errors->first('weight_kg', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}

                    </div>

                </div>

                <div class="form-group{{ $errors->has('height_cm') ? ' has-error' : '' }}">

                    <label for="height_cm" class="col-md-3 control-label">{{ trans('admin/opd_visits/table.height_cm') }}</label>

                    <div class="col-md-8">

                        <input class="form-control" type="number" step="0.01" name="height_cm" id="height_cm"

                               value="{{ old('height_cm', $item->height_cm) }}" />

                        {!! $errors->first('height_cm', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}

                    </div>

                </div>



            </div>



            <div class="tab-pane" id="opd-tab-med-cert" role="tabpanel">



                <p class="col-md-offset-3 col-md-8 help-block ahop-opd-tab-intro">{{ trans('admin/opd_visits/med_cert.med_cert_tab_help') }}</p>



                @if ($item->id)

                    @can('view', $item)

                        <div class="form-group">

                            <div class="col-md-offset-3 col-md-8">

                                <a href="{{ route('opd-visits.medical-certificate', $item) }}" class="btn btn-default btn-sm" target="_blank">

                                    <i class="fas fa-print" aria-hidden="true"></i> {{ trans('admin/opd_visits/med_cert.print_med_cert') }}

                                </a>

                            </div>

                        </div>

                    @endcan

                @endif



                <div class="form-group{{ $errors->has('rest_days') ? ' has-error' : '' }}">

                    <label for="rest_days" class="col-md-3 control-label">{{ trans('admin/opd_visits/med_cert.rest_days') }}</label>

                    <div class="col-md-8">

                        <input class="form-control" type="number" name="rest_days" id="rest_days" min="0" max="365"

                               value="{{ old('rest_days', $item->rest_days) }}" />

                        <p class="help-block">{{ trans('admin/opd_visits/med_cert.rest_days_help') }}</p>

                        {!! $errors->first('rest_days', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}

                    </div>

                </div>

                <div class="form-group{{ $errors->has('med_cert_remarks') ? ' has-error' : '' }}">

                    <label for="med_cert_remarks" class="col-md-3 control-label">{{ trans('admin/opd_visits/med_cert.med_cert_remarks') }}</label>

                    <div class="col-md-8">

                        <textarea class="form-control" name="med_cert_remarks" id="med_cert_remarks" rows="3"

                                  placeholder="{{ trans('admin/opd_visits/med_cert.med_cert_remarks_help') }}">{{ old('med_cert_remarks', $item->med_cert_remarks) }}</textarea>

                        {!! $errors->first('med_cert_remarks', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}

                    </div>

                </div>



            </div>



        </div>

    </div>



@stop



@section('moar_scripts')

<script nonce="{{ csrf_token() }}">

    $(function () {

        @if ($errors->has('rest_days') || $errors->has('med_cert_remarks'))

            $('a[href="#opd-tab-med-cert"]').tab('show');

        @elseif ($errors->has('blood_pressure') || $errors->has('pulse_rate') || $errors->has('temperature') || $errors->has('weight_kg') || $errors->has('height_cm'))

            $('a[href="#opd-tab-vitals"]').tab('show');

        @elseif ($errors->has('visit_number') || $errors->has('patient_id') || $errors->has('physician_id') || $errors->has('visit_date') || $errors->has('visit_type') || $errors->has('status') || $errors->has('chief_complaint') || $errors->has('company_id'))

            $('a[href="#opd-tab-visit"]').tab('show');

        @endif

    });

</script>

@stop


