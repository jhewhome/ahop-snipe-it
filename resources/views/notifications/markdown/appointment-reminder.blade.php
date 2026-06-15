@component('mail::message')
# {{ trans('admin/appointments/mail.reminder_heading') }}

{{ trans('admin/appointments/mail.reminder_intro', ['name' => $patient->full_name ?? '']) }}

@component('mail::panel')
**{{ trans('admin/appointments/table.appointment_number') }}:** {{ $appointment->appointment_number }}

**{{ trans('admin/appointments/table.scheduled_at') }}:** {{ $appointment->scheduled_at?->format('l, F j, Y \a\t g:i A') }}

**{{ trans('admin/appointments/table.visit_type') }}:** {{ \App\Models\Appointment::visitTypeOptions()[$appointment->visit_type] ?? $appointment->visit_type }}

@if ($appointment->physician)
**{{ trans('admin/appointments/table.physician') }}:** {{ $appointment->physician->present()->fullName }}
@endif

@if ($appointment->reason)
**{{ trans('admin/appointments/table.reason') }}:** {{ $appointment->reason }}
@endif
@endcomponent

{{ trans('admin/appointments/mail.reminder_footer', ['site' => $siteName]) }}

{{ $siteName }}
@endcomponent
