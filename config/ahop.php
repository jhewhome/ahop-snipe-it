<?php

/**
 * AgilityCare Health Operations Platform (AHOP) customization.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Clinical sidebar mode
    |--------------------------------------------------------------------------
    |
    | When true, the sidebar focuses on healthcare workflows: patients, OPD,
    | medical equipment (assets), staff, reports, and settings. IT-centric
    | modules (licenses, accessories, components, kits, import) are hidden.
    |
    */
    'clinical_sidebar_mode' => env('AHOP_CLINICAL_SIDEBAR', false),

    /*
    |--------------------------------------------------------------------------
    | Simplified assets submenu
    |--------------------------------------------------------------------------
    |
    | When clinical mode is on, show only essential equipment links
    | (list all, maintenances) instead of full IT deployment statuses.
    |
    */
    'simplify_assets_menu' => env('AHOP_SIMPLIFY_ASSETS_MENU', true),

    /*
    |--------------------------------------------------------------------------
    | Show consumables in clinical mode
    |--------------------------------------------------------------------------
    |
    | Optional: show consumables (supplies) in the sidebar for lab/clinic.
    |
    */
    'show_consumables' => env('AHOP_SHOW_CONSUMABLES', false),

    /*
    |--------------------------------------------------------------------------
    | Simplified reports submenu
    |--------------------------------------------------------------------------
    |
    | Hide license/accessory/depreciation reports when in clinical mode.
    |
    */
    'simplify_reports_menu' => env('AHOP_SIMPLIFY_REPORTS_MENU', true),

    /*
    |--------------------------------------------------------------------------
    | AHOP theme (UI Phase A)
    |--------------------------------------------------------------------------
    */
    'theme_enabled' => env('AHOP_THEME_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Modern UI refresh (Phase B)
    |--------------------------------------------------------------------------
    |
    | Softer cards, Inter typography, refined forms/tables/buttons.
    | Requires theme_enabled. Set false to keep the earlier AHOP look.
    |
    */
    'modern_ui' => env('AHOP_MODERN_UI', true),

    /*
    |--------------------------------------------------------------------------
    | UI Phase C — dashboard widgets, empty states, mobile sidebar, panels
    |--------------------------------------------------------------------------
    */
    'ui_phase_c' => env('AHOP_UI_PHASE_C', true),

    'primary_color' => env('AHOP_PRIMARY_COLOR', '#0d6e7a'),

    'primary_dark' => env('AHOP_PRIMARY_DARK', '#094a52'),

    'accent_color' => env('AHOP_ACCENT_COLOR', '#2eb8a6'),

    'default_site_name' => env('AHOP_SITE_NAME', 'AgilityCare Health Operations Platform'),

    /*
    |--------------------------------------------------------------------------
    | Default clinic / site (Company record)
    |--------------------------------------------------------------------------
    |
    | Matches the companies.name used for reception check-in and med certs.
    | Run: php artisan ahop:seed-companies
    |
    */
    'default_clinic_company_name' => env('AHOP_CLINIC_SITE_NAME', env('AHOP_SITE_NAME', 'AgilityCare Main Clinic')),

    /*
    |--------------------------------------------------------------------------
    | Attending physician dropdown (AHOP role groups)
    |--------------------------------------------------------------------------
    |
    | Users in these AHOP role groups appear in Attending Physician selects.
    | Comma-separated labels without the "AHOP " prefix.
    |
    */
    'physician_role_groups' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('AHOP_PHYSICIAN_ROLE_GROUPS', 'Clinic Staff,Clinic Administrator'))
    ))),

    'tagline' => env('AHOP_TAGLINE', 'Integrated Health & Asset Management'),

    /*
    |--------------------------------------------------------------------------
    | Clinical equipment status in simplified sidebar
    |--------------------------------------------------------------------------
    */
    'show_equipment_status_nav' => env('AHOP_SHOW_EQUIPMENT_STATUS_NAV', true),

    /*
    |--------------------------------------------------------------------------
    | Clinical dashboard (UI Phase B)
    |--------------------------------------------------------------------------
    */
    'clinical_dashboard' => env('AHOP_CLINICAL_DASHBOARD', true),

    /*
    |--------------------------------------------------------------------------
    | Seed clinical equipment via artisan ahop:seed-equipment
    |--------------------------------------------------------------------------
    */
    'seed_clinical_equipment' => env('AHOP_SEED_CLINICAL_EQUIPMENT', false),

    /*
    |--------------------------------------------------------------------------
    | Clinical Analytics (Phase 5; env key AHOP_AI_INSIGHTS for compatibility)
    |--------------------------------------------------------------------------
    */
    'ai_insights_enabled' => env('AHOP_AI_INSIGHTS', true),

    'clinical_analytics_enabled' => env('AHOP_CLINICAL_ANALYTICS', env('AHOP_AI_INSIGHTS', true)),

    /*
    |--------------------------------------------------------------------------
    | Auto-bill OPD visit on completion
    |--------------------------------------------------------------------------
    |
    | When an OPD visit status changes to completed, create/open an invoice
    | with the default consultation fee (CONSULT / FOLLOWUP).
    |
    */
    'auto_bill_on_opd_complete' => env('AHOP_AUTO_BILL_OPD_COMPLETE', true),

    /*
    |--------------------------------------------------------------------------
    | Clinical database (PostgreSQL) — dual-database mode
    |--------------------------------------------------------------------------
    |
    | When enabled, patients, OPD visits, lab orders/results (and future billing)
    | use the PostgreSQL "clinical" connection. Snipe-IT assets/users stay on MySQL.
    |
    | After php artisan migrate, run: php artisan ahop:clinical-db-setup
    |
    */
    'clinical_database' => [
        'enabled' => env('AHOP_CLINICAL_DATABASE_ENABLED', false),
        'connection' => env('CLINICAL_DB_CONNECTION', 'clinical'),
    ],

    'clinical_tables' => [
        'patients',
        'appointments',
        'opd_visits',
        'lab_orders',
        'lab_results',
        'billable_services',
        'billing_invoices',
        'billing_line_items',
        'billing_payments',
    ],

    /*
    |--------------------------------------------------------------------------
    | Priority 1 — security, backups, staff adoption
    |--------------------------------------------------------------------------
    */
    'priority1' => [
        'daily_backup' => env('AHOP_DAILY_BACKUP', true),
        'backup_health_max_age_hours' => (int) env('AHOP_BACKUP_MAX_AGE_HOURS', 26),
        'staff_guide_enabled' => env('AHOP_STAFF_GUIDE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Appointment reminders (email)
    |--------------------------------------------------------------------------
    |
    | Patients need an email on their profile. Requires mail.* configured in .env
    | and php artisan schedule:run (hourly sends ahop:send-appointment-reminders).
    |
    */
    'appointment_reminders' => [
        'enabled' => env('AHOP_APPOINTMENT_REMINDERS', true),
        'hours_before' => (int) env('AHOP_APPOINTMENT_REMINDER_HOURS', 24),
        'log_sms_placeholder' => env('AHOP_LOG_SMS_PLACEHOLDER', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard auto-refresh (Phase B)
    |--------------------------------------------------------------------------
    |
    | Polls /ahop/dashboard-data while the clinical dashboard is open.
    |
    */
    'dashboard_auto_refresh' => [
        'enabled' => env('AHOP_DASHBOARD_AUTO_REFRESH', true),
        'interval_seconds' => (int) env('AHOP_DASHBOARD_REFRESH_SECONDS', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Equipment maintenance alert emails (Phase B)
    |--------------------------------------------------------------------------
    |
    | Sends a daily digest to Settings → Alerts email when high-priority
    | maintenance scores or pending-repair assets are detected.
    |
    */
    'equipment_alerts' => [
        'enabled' => env('AHOP_EQUIPMENT_ALERTS', true),
        'min_score' => (int) env('AHOP_EQUIPMENT_ALERT_MIN_SCORE', 30),
    ],

];
