@extends('layouts/default')

@section('title')
    {{ trans('admin/reception/table.title') }}
    @parent
@stop

@section('content')
<div class="row ahop-reception-layout">
    <div class="ahop-reception-layout__main">
        <div class="box box-default ahop-panel ahop-reception-panel">
            <div class="box-header with-border">
                <h2 class="box-title">{{ trans('admin/reception/table.title') }}</h2>
                <div class="box-tools pull-right">
                    @can('view', \App\Models\OpdVisit::class)
                        <a href="{{ route('opd-visits.queue') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-list-ol" aria-hidden="true"></i> {{ trans('admin/opd_visits/table.queue_title') }}
                        </a>
                    @endcan
                </div>
            </div>
            <div class="box-body">
                <p class="text-muted">{{ trans('admin/reception/table.subtitle') }}</p>

                <div class="callout callout-default ahop-clinical-context-callout ahop-reception-clinic-site">
                    <div class="ahop-reception-clinic-site__row">
                        <label for="clinic_site_id" class="ahop-reception-clinic-site__label">
                            <strong>{{ trans('admin/reception/table.clinic_site') }}</strong>
                        </label>
                        @if ($canSelectClinicSite)
                            <form method="get" action="{{ route('reception.check-in') }}" class="ahop-reception-clinic-site__form">
                                @foreach (request()->except('clinic_site_id') as $key => $value)
                                    @if (is_array($value))
                                        @foreach ($value as $v)
                                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                        @endforeach
                                    @else
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endif
                                @endforeach
                                <select name="clinic_site_id" id="clinic_site_id" class="form-control ahop-reception-clinic-site__select"
                                        onchange="this.form.submit()" aria-label="{{ trans('admin/reception/table.clinic_site') }}">
                                    @foreach ($clinicSites as $site)
                                        <option value="{{ $site->id }}" {{ (int) $activeClinicSiteId === (int) $site->id ? 'selected' : '' }}>
                                            {{ $site->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        @else
                            <p class="ahop-reception-clinic-site__value">{{ $activeClinicSiteName ?: trans('admin/reception/table.clinic_site_not_set') }}</p>
                        @endif
                    </div>
                    <p class="text-muted ahop-reception-clinic-site__help"><small>{{ trans('admin/reception/table.clinic_site_help') }}</small></p>
                </div>

                @if (! $selectedPatient)
                    <div class="ahop-reception-patient-search">
                        <h4>{{ trans('admin/reception/table.find_patient') }}</h4>

                        <div class="ahop-reception-patient-search__control">
                            <input type="search"
                                   id="reception-patient-search"
                                   class="form-control ahop-reception-patient-search__input"
                                   value="{{ $searchQuery ?? request('search') }}"
                                   placeholder="{{ trans('admin/reception/table.search_placeholder') }}"
                                   autocomplete="off"
                                   aria-autocomplete="list"
                                   aria-controls="reception-patient-search-suggestions"
                                   aria-expanded="false"
                                   autofocus>
                            <ul id="reception-patient-search-suggestions"
                                class="ahop-reception-patient-search__suggestions list-unstyled"
                                role="listbox"
                                hidden></ul>
                        </div>

                        <p class="text-muted ahop-reception-patient-search__help">
                            <small>{{ trans('admin/reception/table.search_help') }}</small>
                            <span id="reception-patient-search-status" class="ahop-reception-patient-search__status" aria-live="polite"></span>
                        </p>

                        <div id="reception-patient-search-results" class="ahop-reception-search-results">
                            @include('reception.partials.patient-search-results', [
                                'searchResults' => $searchResults,
                                'search' => $searchQuery ?? request('search'),
                            ])
                        </div>
                    </div>

                    @can('create', \App\Models\Patient::class)
                        <hr>
                        <h4>{{ trans('admin/reception/table.register_patient') }}</h4>
                        <p class="text-muted"><small>{{ trans('admin/reception/table.register_help') }}</small></p>

                        <form method="post" action="{{ route('reception.check-in.patient') }}" class="form-horizontal">
                            @csrf
                            @if ($activeClinicSiteId)
                                <input type="hidden" name="company_id" value="{{ $activeClinicSiteId }}">
                            @endif
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group{{ $errors->has('patient_number') ? ' has-error' : '' }}">
                                        <label for="patient_number">{{ trans('admin/patients/table.patient_number') }}</label>
                                        <input class="form-control" type="text" name="patient_number" id="patient_number"
                                               value="{{ old('patient_number', $nextPatientNumber) }}" required>
                                        {!! $errors->first('patient_number', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                                    </div>
                                    <div class="form-group{{ $errors->has('full_name') ? ' has-error' : '' }}">
                                        <label for="full_name">{{ trans('admin/patients/table.full_name') }}</label>
                                        <input class="form-control" type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" required>
                                        {!! $errors->first('full_name', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                                    </div>
                                    <div class="form-group{{ $errors->has('sex') ? ' has-error' : '' }}">
                                        <label for="sex">{{ trans('admin/patients/table.sex') }}</label>
                                        <select name="sex" id="sex" class="form-control" required>
                                            <option value="">{{ trans('general.select') }}...</option>
                                            <option value="M" {{ old('sex') === 'M' ? 'selected' : '' }}>Male</option>
                                            <option value="F" {{ old('sex') === 'F' ? 'selected' : '' }}>Female</option>
                                        </select>
                                        {!! $errors->first('sex', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                                    </div>
                                    <div class="form-group{{ $errors->has('birthdate') ? ' has-error' : '' }}">
                                        <label for="birthdate">{{ trans('admin/patients/table.birthdate') }}</label>
                                        <input class="form-control" type="date" name="birthdate" id="birthdate" value="{{ old('birthdate') }}" required>
                                        {!! $errors->first('birthdate', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group{{ $errors->has('contact_number') ? ' has-error' : '' }}">
                                        <label for="contact_number">{{ trans('admin/patients/table.contact_number') }}</label>
                                        <input class="form-control" type="text" name="contact_number" id="contact_number" value="{{ old('contact_number') }}">
                                    </div>
                                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                        <label for="email">{{ trans('admin/patients/table.email') }}</label>
                                        <input class="form-control" type="email" name="email" id="email" value="{{ old('email') }}">
                                    </div>
                                    <div class="form-group{{ $errors->has('allergies') ? ' has-error' : '' }}">
                                        <label for="allergies">{{ trans('admin/patients/table.allergies') }}</label>
                                        <textarea class="form-control" name="allergies" id="allergies" rows="2">{{ old('allergies') }}</textarea>
                                    </div>
                                    <div class="form-group{{ $errors->has('problem_list') ? ' has-error' : '' }}">
                                        <label for="problem_list">{{ trans('admin/patients/table.problem_list') }}</label>
                                        <textarea class="form-control" name="problem_list" id="problem_list" rows="2">{{ old('problem_list') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-user-plus" aria-hidden="true"></i> {{ trans('admin/reception/table.register_and_continue') }}
                            </button>
                        </form>
                    @endcan
                @else
                    <div class="clearfix" style="margin-bottom: 16px;">
                        <h4 style="margin-top: 0;">{{ trans('admin/reception/table.selected_patient') }}</h4>
                        <a href="{{ route('reception.check-in') }}" class="btn btn-default btn-sm pull-right">
                            {{ trans('admin/reception/table.change_patient') }}
                        </a>
                    </div>

                    <div class="table-responsive ahop-reception-search-table-wrap">
                        <table class="table table-bordered ahop-reception-detail-table" style="margin-bottom: 16px;">
                        <tr>
                            <th style="width: 30%;">{{ trans('admin/patients/table.full_name') }}</th>
                            <td>{{ $selectedPatient->full_name }} <small class="text-muted">({{ $selectedPatient->patient_number }})</small></td>
                        </tr>
                        <tr>
                            <th>{{ trans('admin/patients/table.contact_number') }}</th>
                            <td>{{ $selectedPatient->contact_number ?: '—' }}</td>
                        </tr>
                        @if ($selectedPatient->company_id && ($patientClinic = $selectedPatient->company ?? \App\Models\Company::find($selectedPatient->company_id)))
                            <tr>
                                <th>{{ trans('admin/reception/table.clinic_site') }}</th>
                                <td>{{ $patientClinic->name }}</td>
                            </tr>
                        @endif
                        @if ($selectedPatient->allergies)
                            <tr>
                                <th>{{ trans('admin/patients/table.allergies') }}</th>
                                <td class="text-danger"><strong>{{ $selectedPatient->allergies }}</strong></td>
                            </tr>
                        @endif
                        @if ($selectedPatient->problem_list)
                            <tr>
                                <th>{{ trans('admin/patients/table.problem_list') }}</th>
                                <td>{{ $selectedPatient->problem_list }}</td>
                            </tr>
                        @endif
                    </table>
                    </div>

                    <p>
                        <a href="{{ route('patients.show', $selectedPatient) }}" class="btn btn-default btn-sm">
                            {{ trans('admin/reception/table.view_profile') }}
                        </a>
                    </p>

                    @if ($activeVisit)
                        <div class="callout callout-info">
                            <h4 style="margin-top: 0;">{{ trans('admin/reception/table.active_visit') }}</h4>
                            <p style="margin-bottom: 8px;">
                                {{ $activeVisit->visit_number }}
                                — {{ \App\Models\OpdVisit::statusOptions()[$activeVisit->status] ?? $activeVisit->status }}
                            </p>
                            <a href="{{ route('opd-visits.show', $activeVisit) }}" class="btn btn-sm btn-primary">
                                {{ trans('admin/reception/table.open_visit') }}
                            </a>
                        </div>

                        <div class="callout callout-success ahop-clinical-context-callout" style="margin-bottom: 16px;">
                            <h4 style="margin-top: 0;">{{ trans('admin/reception/table.whats_next_title') }}</h4>
                            <p style="margin-bottom: 12px;"><strong>{{ trans('admin/reception/table.reception_handoff_note') }}</strong></p>
                            <ol style="margin-bottom: 12px; padding-left: 20px;">
                                <li>{{ trans('admin/reception/table.whats_next_1') }}</li>
                                <li>{{ trans('admin/reception/table.whats_next_2') }}</li>
                                <li>{{ trans('admin/reception/table.whats_next_3') }}</li>
                            </ol>
                            @can('view', \App\Models\OpdVisit::class)
                                <a href="{{ route('opd-visits.queue') }}" class="btn btn-sm btn-default">
                                    <i class="fas fa-list-ol" aria-hidden="true"></i> {{ trans('admin/reception/table.opd_queue_link') }}
                                </a>
                            @endcan
                        </div>
                    @endif

                    <h4>{{ trans('admin/reception/table.today_appointments') }}</h4>
                    @if ($patientAppointments->count())
                        <div class="table-responsive ahop-reception-search-table-wrap">
                            <table class="table table-bordered ahop-reception-patient-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('admin/appointments/table.scheduled_at') }}</th>
                                    <th>{{ trans('admin/appointments/table.physician') }}</th>
                                    <th>{{ trans('admin/appointments/table.status') }}</th>
                                    <th>{{ trans('admin/appointments/table.reason') }}</th>
                                    <th class="text-right">{{ trans('table.actions') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($patientAppointments as $appt)
                                    <tr>
                                        <td>{{ $appt->scheduled_at?->format('H:i') }}</td>
                                        <td>{{ $appt->physician?->present()->fullName ?? '—' }}</td>
                                        <td>
                                            <span class="ahop-badge ahop-badge-{{ $appt->status }}">
                                                {{ \App\Models\Appointment::statusOptions()[$appt->status] ?? $appt->status }}
                                            </span>
                                        </td>
                                        <td>{{ $appt->reason ?: '—' }}</td>
                                        <td class="text-right">
                                            @if ($appt->canCheckIn())
                                                @can('update', $appt)
                                                    <form method="post" action="{{ route('reception.check-in.appointment', $appt) }}" style="display:inline;"
                                                          onsubmit="return confirm('{{ trans('admin/reception/message.check_in_confirm') }}');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fas fa-door-open" aria-hidden="true"></i> {{ trans('admin/appointments/table.check_in') }}
                                                        </button>
                                                    </form>
                                                @endcan
                                            @elseif ($appt->status === \App\Models\Appointment::STATUS_CHECKED_IN)
                                                <span class="text-muted">{{ trans('admin/reception/table.checked_in_status') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">{{ trans('admin/reception/table.no_appointments_today') }}</p>
                    @endif

                    @can('create', \App\Models\OpdVisit::class)
                        @if (! $activeVisit)
                            <hr>
                            <h4>{{ trans('admin/reception/table.walk_in_check_in') }}</h4>
                            <p class="text-muted"><small>{{ trans('admin/reception/table.walk_in_help') }}</small></p>

                            <form method="post" action="{{ route('reception.check-in.walk-in') }}" class="form-horizontal">
                                @csrf
                                <input type="hidden" name="patient_id" value="{{ $selectedPatient->id }}">
                                @if ($activeClinicSiteId)
                                    <input type="hidden" name="company_id" value="{{ $activeClinicSiteId }}">
                                @endif

                                @include('partials.forms.edit.physician-select', [
                                    'translated_name' => trans('admin/opd_visits/table.physician'),
                                    'fieldname' => 'physician_id',
                                    'item' => (object) ['physician_id' => old('physician_id', $defaultPhysicianId ?? null)],
                                    'physicians' => $physicians ?? collect(),
                                    'placeholder' => trans('admin/reception/table.physician_placeholder'),
                                    'help_text' => trans('admin/reception/table.physician_help'),
                                ])

                                <div class="form-group{{ $errors->has('chief_complaint') ? ' has-error' : '' }}">
                                    <label for="chief_complaint" class="col-md-3 control-label">{{ trans('admin/reception/table.chief_complaint') }}</label>
                                    <div class="col-md-8">
                                        <textarea class="form-control" name="chief_complaint" id="chief_complaint" rows="2"
                                                  placeholder="{{ trans('admin/reception/table.chief_complaint_placeholder') }}">{{ old('chief_complaint') }}</textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-8 col-md-offset-3">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-door-open" aria-hidden="true"></i> {{ trans('admin/reception/table.check_in_walk_in') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    @endcan
                @endif
            </div>
        </div>
    </div>

    <div class="ahop-reception-layout__aside">
        <div class="box box-default ahop-panel ahop-reception-queue-panel">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('admin/reception/table.today_queue') }}</h3>
            </div>
            <div class="box-body">
                <p class="text-muted">
                    <small>{{ trans('admin/reception/table.queue_count', ['count' => $opdQueueCount]) }}</small>
                </p>
                @can('view', \App\Models\OpdVisit::class)
                    <p style="margin-bottom: 16px;">
                        <a href="{{ route('opd-visits.queue') }}">{{ trans('admin/reception/table.opd_queue_link') }}</a>
                    </p>
                @endcan

                @if ($todayQueue->count())
                    <div class="table-responsive">
                        <table class="table table-condensed table-bordered">
                            <thead>
                            <tr>
                                <th>{{ trans('admin/appointments/table.scheduled_at') }}</th>
                                <th>{{ trans('admin/appointments/table.patient') }}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($todayQueue as $appt)
                                <tr>
                                    <td>{{ $appt->scheduled_at?->format('H:i') }}</td>
                                    <td>
                                        @if ($appt->patient)
                                            <a href="{{ route('reception.check-in', ['patient_id' => $appt->patient_id]) }}">
                                                {{ $appt->patient->full_name }}
                                            </a>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if ($appt->canCheckIn())
                                            <span class="label label-warning">{{ trans('admin/appointments/table.status_scheduled') }}</span>
                                        @else
                                            <span class="label label-success">{{ trans('admin/reception/table.checked_in_status') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">{{ trans('admin/appointments/table.no_appointments') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
(function () {
    var input = document.getElementById('reception-patient-search');
    if (! input) {
        return;
    }

    var suggestions = document.getElementById('reception-patient-search-suggestions');
    var results = document.getElementById('reception-patient-search-results');
    var status = document.getElementById('reception-patient-search-status');
    var searchUrl = @json(route('reception.check-in.search'));
    var debounceTimer = null;
    var activeRequest = null;
    var hideSuggestionsTimer = null;

    function setStatus(message) {
        if (status) {
            status.textContent = message ? (' ' + message) : '';
        }
    }

    function hideSuggestions() {
        suggestions.innerHTML = '';
        suggestions.hidden = true;
        input.setAttribute('aria-expanded', 'false');
    }

    function renderSuggestions(items) {
        suggestions.innerHTML = '';

        if (! items.length) {
            hideSuggestions();
            return;
        }

        items.slice(0, 8).forEach(function (item) {
            var li = document.createElement('li');
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'ahop-reception-patient-search__suggestion';
            button.setAttribute('role', 'option');
            button.dataset.url = item.url;
            button.innerHTML = '<strong>' + escapeHtml(item.full_name) + '</strong>'
                + '<span>' + escapeHtml(item.patient_number)
                + (item.contact_number ? ' · ' + escapeHtml(item.contact_number) : '')
                + '</span>';
            button.addEventListener('mousedown', function (event) {
                event.preventDefault();
                window.location.href = item.url;
            });
            li.appendChild(button);
            suggestions.appendChild(li);
        });

        suggestions.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function runSearch() {
        var query = input.value.trim();

        if (query.length < 2) {
            if (activeRequest) {
                activeRequest.abort();
                activeRequest = null;
            }
            hideSuggestions();
            results.innerHTML = '';
            setStatus(@json(trans('admin/reception/table.type_to_search')));
            return;
        }

        setStatus(@json(trans('admin/reception/table.searching')));

        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = $.ajax({
            url: searchUrl,
            data: { q: query },
            dataType: 'json',
        }).done(function (payload) {
            results.innerHTML = payload.html || '';
            renderSuggestions(payload.results || []);
            setStatus((payload.results || []).length
                ? @json(trans('admin/reception/table.results_count')).replace(':count', String((payload.results || []).length))
                : @json(trans('admin/reception/table.no_results')));
        }).fail(function (xhr, textStatus) {
            if (textStatus === 'abort') {
                return;
            }
            hideSuggestions();
            setStatus(@json(trans('admin/reception/table.search_error')));
        }).always(function () {
            activeRequest = null;
        });
    }

    input.addEventListener('input', function () {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(runSearch, 300);
    });

    input.addEventListener('focus', function () {
        if (suggestions.children.length) {
            suggestions.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }
    });

    input.addEventListener('blur', function () {
        hideSuggestionsTimer = window.setTimeout(hideSuggestions, 150);
    });

    input.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            hideSuggestions();
        }
    });

    if (input.value.trim().length >= 2) {
        runSearch();
    }
})();
</script>
@stop
