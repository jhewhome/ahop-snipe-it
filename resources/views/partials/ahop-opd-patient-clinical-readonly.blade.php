@if ($patient && ($patient->allergies || $patient->problem_list))
<div class="callout ahop-opd-clinical-panel ahop-clinical-context-callout" style="margin-bottom: 16px;">
    <h4 style="margin-top: 0;">{{ trans('admin/opd_visits/table.patient_clinical_context') }}</h4>
    @if ($patient->allergies)
        <p style="margin-bottom: 8px;">
            <strong>{{ trans('admin/patients/table.allergies') }}:</strong>
            <span class="text-danger">{{ $patient->allergies }}</span>
        </p>
    @endif
    @if ($patient->problem_list)
        <p style="margin-bottom: 0;">
            <strong>{{ trans('admin/patients/table.problem_list') }}:</strong>
            {{ $patient->problem_list }}
        </p>
    @endif
</div>
@endif
