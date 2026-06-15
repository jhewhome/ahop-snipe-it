<div id="ahop-opd-clinical-panel" class="callout ahop-opd-clinical-panel ahop-clinical-context-callout" style="display: none; margin-bottom: 16px;">
    <h4 style="margin-top: 0;">{{ trans('admin/opd_visits/table.patient_clinical_context') }}</h4>
    <div class="row">
        <div class="col-md-6">
            <strong>{{ trans('admin/patients/table.allergies') }}</strong>
            <p id="ahop-opd-allergies" class="text-muted" style="margin-bottom: 8px;">—</p>
        </div>
        <div class="col-md-6">
            <strong>{{ trans('admin/patients/table.problem_list') }}</strong>
            <p id="ahop-opd-problems" class="text-muted" style="margin-bottom: 8px;">—</p>
        </div>
    </div>
    <p class="text-muted" style="margin-bottom: 0;">
        <small><i class="fas fa-info-circle" aria-hidden="true"></i> {{ trans('admin/opd_visits/table.patient_clinical_note') }}</small>
    </p>
</div>

@if (!empty($patientClinicalMap))
@push('js')
<script nonce="{{ csrf_token() }}">
    $(function () {
        var clinicalMap = @json($patientClinicalMap);
        var $panel = $('#ahop-opd-clinical-panel');
        var $patientSelect = $('#patient_id');

        function updateClinicalPanel(patientId) {
            var data = clinicalMap[patientId] || clinicalMap[String(patientId)];
            if (!data) {
                $panel.hide();
                return;
            }

            var allergies = (data.allergies || '').trim();
            var problems = (data.problem_list || '').trim();
            $('#ahop-opd-allergies').text(allergies || '{{ trans('admin/opd_visits/table.no_allergies_recorded') }}');
            $('#ahop-opd-problems').text(problems || '{{ trans('admin/opd_visits/table.no_problems_recorded') }}');

            if (allergies) {
                $('#ahop-opd-allergies').removeClass('text-muted').addClass('text-danger');
            } else {
                $('#ahop-opd-allergies').removeClass('text-danger').addClass('text-muted');
            }

            $panel.show();
        }

        $patientSelect.on('change', function () {
            updateClinicalPanel($(this).val());
        });

        if ($patientSelect.val()) {
            updateClinicalPanel($patientSelect.val());
        }
    });
</script>
@endpush
@endif
