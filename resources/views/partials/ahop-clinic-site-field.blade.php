{{-- Clinic / site — set at reception check-in; read-only on visit unless admin can change --}}
@php
    $companyId = old($fieldname ?? 'company_id', $item->company_id ?? null);
    $companyName = $companyId ? \App\Models\Company::find($companyId)?->name : null;
    $fieldname = $fieldname ?? 'company_id';
    $canChange = \App\Models\Company::canManageUsersCompanies() && empty($readonly);
@endphp

@if ($companyId && $companyName && ! $canChange)
    <div class="form-group">
        <label class="col-md-3 control-label">{{ $translated_name ?? trans('admin/opd_visits/table.clinic_site') }}</label>
        <div class="col-md-8">
            <p class="form-control-static" style="padding-top: 7px;">{{ $companyName }}</p>
            <input type="hidden" name="{{ $fieldname }}" value="{{ $companyId }}">
            @if (! empty($help_text))
                <p class="help-block">{{ $help_text }}</p>
            @else
                <p class="help-block">{{ trans('admin/opd_visits/table.clinic_site_intake_help') }}</p>
            @endif
        </div>
    </div>
@elseif ($canChange)
    @include ('partials.forms.edit.company-select', [
        'translated_name' => $translated_name ?? trans('admin/opd_visits/table.clinic_site'),
        'fieldname' => $fieldname,
    ])
    @if (! empty($help_text))
        <div class="form-group">
            <div class="col-md-8 col-md-offset-3">
                <p class="help-block">{{ $help_text }}</p>
            </div>
        </div>
    @endif
@elseif ($companyId && $companyName)
    <div class="form-group">
        <label class="col-md-3 control-label">{{ $translated_name ?? trans('admin/opd_visits/table.clinic_site') }}</label>
        <div class="col-md-8">
            <p class="form-control-static" style="padding-top: 7px;">{{ $companyName }}</p>
            <input type="hidden" name="{{ $fieldname }}" value="{{ $companyId }}">
        </div>
    </div>
@endif
