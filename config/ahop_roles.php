<?php

/**
 * AHOP clinical role templates for Priority 1 (security & staff adoption).
 *
 * Run: php artisan ahop:setup-clinical-roles
 *
 * Permissions use Snipe-IT keys from config/permissions.php ('1' = granted).
 */
return [

    'prefix' => env('AHOP_ROLE_GROUP_PREFIX', 'AHOP '),

    'roles' => [

        'Reception' => [
            'notes' => 'Front desk: patients, appointments, OPD, billing (create/view/edit). No delete access.',
            'permissions' => [
                'patients.view' => '1',
                'patients.create' => '1',
                'patients.edit' => '1',
                'appointments.view' => '1',
                'appointments.create' => '1',
                'appointments.edit' => '1',
                'opd_visits.view' => '1',
                'opd_visits.create' => '1',
                'opd_visits.edit' => '1',
                'lab_orders.view' => '1',
                'billing_invoices.view' => '1',
                'billing_invoices.create' => '1',
                'billing_invoices.edit' => '1',
            ],
        ],

        'Clinic Staff' => [
            'notes' => 'Nurses and physicians: appointments, OPD documentation, lab requests, and patient updates.',
            'permissions' => [
                'patients.view' => '1',
                'patients.edit' => '1',
                'appointments.view' => '1',
                'appointments.create' => '1',
                'appointments.edit' => '1',
                'opd_visits.view' => '1',
                'opd_visits.create' => '1',
                'opd_visits.edit' => '1',
                'lab_orders.view' => '1',
                'lab_orders.create' => '1',
                'ai_insights.view' => '1',
            ],
        ],

        'Laboratory' => [
            'notes' => 'Lab technicians: orders and result entry.',
            'permissions' => [
                'patients.view' => '1',
                'opd_visits.view' => '1',
                'lab_orders.view' => '1',
                'lab_orders.create' => '1',
                'lab_orders.edit' => '1',
                'ai_insights.view' => '1',
            ],
        ],

        'Biomedical' => [
            'notes' => 'Medical equipment and maintenance tracking.',
            'permissions' => [
                'assets.view' => '1',
                'assets.edit' => '1',
                'assets.checkin' => '1',
                'assets.checkout' => '1',
                'reports.view' => '1',
                'ai_insights.view' => '1',
            ],
        ],

        'Clinic Administrator' => [
            'notes' => 'Clinic manager: full clinical and billing access, operational reports.',
            'permissions' => [
                'patients.view' => '1',
                'patients.create' => '1',
                'patients.edit' => '1',
                'patients.delete' => '1',
                'appointments.view' => '1',
                'appointments.create' => '1',
                'appointments.edit' => '1',
                'appointments.delete' => '1',
                'opd_visits.view' => '1',
                'opd_visits.create' => '1',
                'opd_visits.edit' => '1',
                'opd_visits.delete' => '1',
                'lab_orders.view' => '1',
                'lab_orders.create' => '1',
                'lab_orders.edit' => '1',
                'lab_orders.delete' => '1',
                'billing_invoices.view' => '1',
                'billing_invoices.create' => '1',
                'billing_invoices.edit' => '1',
                'billing_invoices.delete' => '1',
                'assets.view' => '1',
                'reports.view' => '1',
                'ai_insights.view' => '1',
            ],
        ],

    ],

];
