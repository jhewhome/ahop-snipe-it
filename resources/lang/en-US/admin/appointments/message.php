<?php

return [
    'create' => [
        'success' => 'Appointment scheduled successfully.',
    ],
    'update' => [
        'success' => 'Appointment updated successfully.',
    ],
    'delete' => [
        'success' => 'Appointment deleted successfully.',
        'error' => 'Appointment could not be deleted.',
        'confirm' => 'Are you sure you want to delete this appointment?',
    ],
    'check_in' => [
        'success' => 'Patient checked in. OPD visit :visit created.',
        'invalid_status' => 'This appointment cannot be checked in.',
        'opd_failed' => 'Could not create OPD visit.',
        'appointment_failed' => 'OPD visit created but appointment could not be updated.',
        'confirm' => 'Check in this patient and start an OPD visit?',
    ],
    'billing' => [
        'created' => 'Invoice created with default consultation charge. Add more charges or record payment.',
        'opened' => 'Opened existing invoice for this appointment.',
        'failed' => 'Could not create billing invoice for this appointment.',
        'confirm' => 'Create a billing invoice for this appointment with the default consultation fee?',
    ],
    'reminder' => [
        'sent' => 'Appointment reminder email sent.',
        'no_email' => 'Add a patient email address before sending a reminder.',
        'disabled' => 'Appointment reminders are disabled in system settings.',
        'invalid_status' => 'Reminders can only be sent for scheduled appointments.',
        'schema_missing' => 'Appointment reminder database update is missing. Ask an administrator to run migrations on the server.',
        'mail_not_configured' => 'Outgoing email is not configured (MAIL_FROM_ADDR / MAIL_* in server .env).',
        'mail_failed' => 'The reminder email could not be delivered. Check server mail settings and try again.',
        'failed' => 'Could not send the reminder.',
        'confirm' => 'Send appointment reminder email to the patient now?',
        'already_sent' => 'Reminder sent',
        'not_sent' => 'No reminder sent yet',
    ],
];
