<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $visit->visit_number }} — {{ trans('admin/opd_visits/med_cert.med_cert_title') }}</title>
    <style>
        :root {
            --ahop-primary: #0d6e7a;
            --ahop-primary-dark: #094a52;
            --ahop-border: #d8e2e8;
            --ahop-muted: #5c6b73;
        }
        * { box-sizing: border-box; }
        body {
            font-family: "Segoe UI", Georgia, "Times New Roman", serif;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            padding: 24px;
            color: #1a2b33;
            background: #f4f8fa;
        }
        .toolbar {
            max-width: 780px;
            margin: 0 auto 16px;
            text-align: right;
        }
        .toolbar a,
        .toolbar button {
            display: inline-block;
            margin-left: 8px;
            padding: 8px 14px;
            border-radius: 4px;
            text-decoration: none;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
            cursor: pointer;
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 13px;
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }
        .toolbar a:hover,
        .toolbar a:focus,
        .toolbar button:not(.primary):hover,
        .toolbar button:not(.primary):focus {
            background: #f1f5f9;
            border-color: var(--ahop-primary);
            color: var(--ahop-primary-dark);
            text-decoration: none;
        }
        .toolbar button.primary {
            background: var(--ahop-primary);
            color: #fff;
            border-color: var(--ahop-primary);
        }
        .toolbar button.primary:hover,
        .toolbar button.primary:focus {
            background: var(--ahop-primary-dark);
            border-color: var(--ahop-primary-dark);
            color: #fff;
        }
        .toolbar .hint {
            display: block;
            margin-top: 10px;
            text-align: left;
            color: var(--ahop-muted);
            font-size: 12px;
            font-family: "Segoe UI", Arial, sans-serif;
        }
        .certificate {
            max-width: 780px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid var(--ahop-border);
            border-radius: 8px;
            padding: 40px 48px 48px;
        }
        .letterhead {
            text-align: center;
            border-bottom: 3px solid var(--ahop-primary);
            padding-bottom: 18px;
            margin-bottom: 28px;
        }
        .letterhead .clinic-name {
            margin: 0 0 6px;
            font-size: 24px;
            font-weight: 700;
            color: var(--ahop-primary-dark);
            letter-spacing: 0.02em;
        }
        .letterhead .clinic-subtitle {
            margin: 0;
            font-size: 13px;
            color: var(--ahop-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .doc-title {
            text-align: center;
            margin: 0 0 28px;
            font-size: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #1a2b33;
        }
        .meta-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .meta-grid th,
        .meta-grid td {
            padding: 8px 10px;
            border: 1px solid var(--ahop-border);
            text-align: left;
            vertical-align: top;
        }
        .meta-grid th {
            width: 34%;
            background: #eef6f8;
            font-weight: 600;
        }
        .body-text {
            margin: 0 0 16px;
            text-align: justify;
        }
        .diagnosis-box {
            border: 1px solid var(--ahop-border);
            background: #fafcfd;
            padding: 14px 16px;
            margin: 18px 0 24px;
            border-radius: 6px;
        }
        .diagnosis-box strong {
            display: block;
            margin-bottom: 6px;
            color: var(--ahop-primary-dark);
        }
        .remarks {
            margin: 0 0 24px;
            white-space: pre-wrap;
        }
        .signature-block {
            margin-top: 48px;
            width: 280px;
            margin-left: auto;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #334155;
            margin-bottom: 8px;
            height: 1px;
        }
        .signature-name {
            font-weight: 700;
            margin: 0 0 4px;
        }
        .signature-role {
            margin: 0;
            color: var(--ahop-muted);
            font-size: 13px;
        }
        .issued-on {
            margin-top: 24px;
            font-size: 13px;
            color: var(--ahop-muted);
        }
        .muted { color: var(--ahop-muted); font-style: italic; }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .certificate { border: none; border-radius: 0; padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
@php
    $patient = $visit->patient;
    $physicianName = $visit->physician?->present()->fullName;
    $clinicCompany = $visit->company;
    $clinicName = $clinicCompany?->name
        ?: config('ahop.default_clinic_company_name')
        ?: (($snipeSettings->site_name ?? '') ?: config('ahop.default_site_name', 'AgilityCare Health Operations Platform'));
    $clinicContact = collect([
        $clinicCompany?->phone,
        $clinicCompany?->email,
    ])->filter()->implode(' · ');
    $visitDate = $visit->visit_date;
    $restDays = $visit->rest_days;
    $restUntil = ($visitDate && $restDays) ? $visitDate->copy()->addDays((int) $restDays)->format('F j, Y') : null;
    $patientAge = $patient?->birthdate?->age;
@endphp

<div class="toolbar">
    <button type="button" class="primary" onclick="window.print();">{{ trans('admin/opd_visits/med_cert.print_med_cert') }}</button>
    @if (! $visit->diagnosis || ! $physicianName)
        <span class="hint">{{ trans('admin/opd_visits/med_cert.med_cert_missing_hint') }}</span>
    @endif
</div>

<div class="certificate">
    <header class="letterhead">
        <p class="clinic-name">{{ $clinicName }}</p>
        @if ($clinicContact)
            <p class="clinic-subtitle">{{ $clinicContact }}</p>
        @endif
        <p class="clinic-subtitle">{{ trans('general.opd_visits') }} · {{ trans('admin/opd_visits/med_cert.med_cert_reference') }} {{ $visit->visit_number }}</p>
    </header>

    <h1 class="doc-title">{{ trans('admin/opd_visits/med_cert.med_cert_title') }}</h1>

    <table class="meta-grid">
        <tr>
            <th>{{ trans('admin/patients/table.full_name') }}</th>
            <td>{{ $patient?->full_name ?? trans('admin/opd_visits/med_cert.med_cert_not_specified') }}</td>
        </tr>
        <tr>
            <th>{{ trans('admin/patients/table.patient_number') }}</th>
            <td>{{ $patient?->patient_number ?? '—' }}</td>
        </tr>
        <tr>
            <th>{{ trans('admin/patients/table.sex') }}</th>
            <td>{{ $patient?->sex === 'M' ? 'Male' : ($patient?->sex === 'F' ? 'Female' : '—') }}</td>
        </tr>
        @if ($patientAge !== null)
            <tr>
                <th>{{ trans('admin/opd_visits/med_cert.med_cert_patient_age') }}</th>
                <td>{{ $patientAge }}</td>
            </tr>
        @endif
        <tr>
            <th>{{ trans('admin/opd_visits/med_cert.med_cert_examined_on') }}</th>
            <td>{{ $visitDate?->format('F j, Y') ?? trans('admin/opd_visits/med_cert.med_cert_not_specified') }}</td>
        </tr>
        <tr>
            <th>{{ trans('admin/opd_visits/table.physician') }}</th>
            <td>{{ $physicianName ?: trans('admin/opd_visits/med_cert.med_cert_not_specified') }}</td>
        </tr>
    </table>

    <p class="body-text">
        {{ trans('admin/opd_visits/med_cert.med_cert_body_intro', ['date' => $visitDate?->format('F j, Y') ?? trans('admin/opd_visits/med_cert.med_cert_not_specified')]) }}
    </p>

    <div class="diagnosis-box">
        <strong>{{ trans('admin/opd_visits/med_cert.med_cert_diagnosis_line') }}</strong>
        @if ($visit->diagnosis)
            {{ $visit->diagnosis }}
        @else
            <span class="muted">{{ trans('admin/opd_visits/med_cert.med_cert_not_specified') }}</span>
        @endif
    </div>

    @if ($restDays !== null && $restDays > 0)
        <p class="body-text">
            {{ trans('admin/opd_visits/med_cert.med_cert_rest_line', ['days' => $restDays]) }}
            @if ($restUntil)
                {{ trans('admin/opd_visits/med_cert.med_cert_rest_until') }}: <strong>{{ $restUntil }}</strong>.
            @endif
        </p>
    @else
        <p class="body-text muted">{{ trans('admin/opd_visits/med_cert.med_cert_rest_none') }}</p>
    @endif

    @if ($visit->med_cert_remarks)
        <p class="remarks">{{ $visit->med_cert_remarks }}</p>
    @endif

    <p class="body-text">{{ trans('admin/opd_visits/med_cert.med_cert_footer') }}</p>

    <div class="signature-block">
        <div class="signature-line"></div>
        <p class="signature-name">{{ $physicianName ?: '_________________________' }}</p>
        <p class="signature-role">{{ trans('admin/opd_visits/med_cert.med_cert_signature') }}</p>
    </div>

    <p class="issued-on">{{ trans('admin/patients/table.summary_generated') }} {{ now()->format('F j, Y') }}</p>
</div>
</body>
</html>
