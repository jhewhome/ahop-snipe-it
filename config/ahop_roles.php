<?php

/**
 * AHOP clinical role templates for Priority 1 (security & staff adoption).
 *
 * Run: php artisan ahop:setup-clinical-roles --force
 *
 * Permissions use Snipe-IT keys from config/permissions.php ('1' = granted).
 */

$assetViewPermissions = [
    'assets.view' => '1',
    'statuslabels.view' => '1',
    'models.view' => '1',
    'categories.view' => '1',
    'manufacturers.view' => '1',
    'locations.view' => '1',
];

$assetManagePermissions = array_merge($assetViewPermissions, [
    'assets.create' => '1',
    'assets.edit' => '1',
    'assets.checkin' => '1',
    'assets.checkout' => '1',
]);

return [

    'prefix' => env('AHOP_ROLE_GROUP_PREFIX', 'AHOP '),

    'roles' => [

        'Reception' => [
            'notes' => 'Front desk: patients, appointments, OPD, billing; read-only medical equipment list.',
            'permissions' => array_merge([
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
            ], $assetViewPermissions),
        ],

        'Clinic Staff' => [
            'notes' => 'Nurses and physicians: clinical workflows plus read-only equipment registry.',
            'permissions' => array_merge([
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
            ], $assetViewPermissions),
        ],

        'Laboratory' => [
            'notes' => 'Lab technicians: orders, results, and read-only lab equipment list.',
            'permissions' => array_merge([
                'patients.view' => '1',
                'opd_visits.view' => '1',
                'lab_orders.view' => '1',
                'lab_orders.create' => '1',
                'lab_orders.edit' => '1',
                'ai_insights.view' => '1',
            ], $assetViewPermissions),
        ],

        'Biomedical' => [
            'notes' => 'Medical equipment and IT asset registry: full create, edit, checkout, and maintenance.',
            'permissions' => array_merge($assetManagePermissions, [
                'reports.view' => '1',
                'ai_insights.view' => '1',
            ]),
        ],

        'Clinic Administrator' => [
            'notes' => 'Clinic manager: full clinical, billing, and medical equipment registry access.',
            'permissions' => array_merge([
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
                'reports.view' => '1',
                'ai_insights.view' => '1',
            ], $assetManagePermissions),
        ],

    ],

];
