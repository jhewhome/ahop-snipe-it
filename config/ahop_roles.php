<?php

/**
 * AHOP clinical role templates for Priority 1 (security & staff adoption).
 *
 * Run: php artisan ahop:setup-clinical-roles --force
 *
 * Permissions use Snipe-IT keys from config/permissions.php ('1' = granted).
 */

$assetListOnlyPermissions = [
    'assets.view' => '1',
];

$assetViewPermissions = array_merge($assetListOnlyPermissions, [
    'statuslabels.view' => '1',
    'models.view' => '1',
    'categories.view' => '1',
    'manufacturers.view' => '1',
    'locations.view' => '1',
]);

$assetManagePermissions = array_merge($assetViewPermissions, [
    'assets.create' => '1',
    'assets.edit' => '1',
    'assets.checkin' => '1',
    'assets.checkout' => '1',
]);

$assetRegistrySettingsPermissions = [
    'statuslabels.view' => '1',
    'statuslabels.create' => '1',
    'statuslabels.edit' => '1',
    'models.view' => '1',
    'models.create' => '1',
    'models.edit' => '1',
    'categories.view' => '1',
    'categories.create' => '1',
    'categories.edit' => '1',
    'manufacturers.view' => '1',
    'manufacturers.create' => '1',
    'manufacturers.edit' => '1',
    'locations.view' => '1',
    'locations.create' => '1',
    'locations.edit' => '1',
    'suppliers.view' => '1',
    'suppliers.create' => '1',
    'suppliers.edit' => '1',
    'departments.view' => '1',
    'departments.create' => '1',
    'departments.edit' => '1',
    'depreciations.view' => '1',
    'customfields.view' => '1',
    'companies.view' => '1',
];

$biomedicalPermissions = array_merge([
    'assets.view' => '1',
    'assets.create' => '1',
    'assets.edit' => '1',
    'assets.checkin' => '1',
    'assets.checkout' => '1',
], $assetRegistrySettingsPermissions);

return [

    'prefix' => env('AHOP_ROLE_GROUP_PREFIX', 'AHOP '),

    'roles' => [

        'Reception' => [
            'notes' => 'Front desk: patients, appointments, OPD, billing; no medical equipment registry access.',
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
            ], $assetListOnlyPermissions),
        ],

        'Laboratory' => [
            'notes' => 'Lab technicians: orders and results; no medical equipment registry access.',
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
            'notes' => 'Medical equipment registry: assets, maintenance, checkout/checkin, equipment dashboard, and full equipment Settings pages (create/edit status labels, models, categories, manufacturers, locations, suppliers, departments). No clinical modules or reports.',
            'permissions' => $biomedicalPermissions,
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
