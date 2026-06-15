# AgilityCare Health Operations Platform (AHOP)
## Source Code & Documentation

**Version:** 1.0  
**Date:** June 2026  
**Repository:** https://github.com/jhewhome/ahop-snipe-it  
**Live deployment:** http://35.247.142.84  
**Local development:** http://localhost/snipe-it/public  

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [System Overview](#2-system-overview)
3. [Technology Stack](#3-technology-stack)
4. [System Architecture](#4-system-architecture)
5. [Source Code Structure](#5-source-code-structure)
6. [Clinical Modules](#6-clinical-modules)
7. [Database Design](#7-database-design)
8. [Security & Access Control](#8-security--access-control)
9. [API & Integration](#9-api--integration)
10. [Artisan Commands](#10-artisan-commands)
11. [Configuration Reference](#11-configuration-reference)
12. [Deployment Summary](#12-deployment-summary)
13. [Requirements Traceability](#13-requirements-traceability)
14. [Source Code Inventory](#14-source-code-inventory)

---

## 1. Executive Summary

The **AgilityCare Health Operations Platform (AHOP)** is a healthcare information system built as a customization of the open-source **Snipe-IT** asset management platform (Laravel 11). AHOP extends Snipe-IT with integrated clinical workflows:

- Patient registration and EMR-lite records
- Outpatient Department (OPD) visits and queue
- Appointment scheduling and reception check-in
- Laboratory orders and results
- Billing and payment tracking
- Medical equipment and supplies (via Snipe-IT assets module)
- Clinical dashboard, analytics, and operational reports

The system is deployed on **Google Cloud Platform** for demonstration and supports local development on **XAMPP (Windows)**. Source code is version-controlled on **GitHub**.

---

## 2. System Overview

### 2.1 Purpose

AHOP centralizes clinic operations: patient data, clinical encounters, laboratory workflows, billing, and biomedical equipment tracking in a single web application with role-based access for reception, clinical staff, laboratory, biomedical engineering, and clinic administration.

### 2.2 Users and Roles

| Role group | Primary functions |
|------------|-------------------|
| AHOP Reception | Patients, appointments, check-in, billing (create/view) |
| AHOP Clinic Staff | OPD documentation, patient updates, appointments |
| AHOP Laboratory | Lab orders, result entry |
| AHOP Biomedical | Medical equipment assets and maintenance |
| AHOP Clinic Administrator | Full clinical and billing access, reports |
| Superuser (IT) | System settings, user management |

### 2.3 Demo Accounts (after seeding)

| Username | Password | Role |
|----------|----------|------|
| `clinicadmin` | `demo1234` | Clinic Administrator |
| `reception` | `demo1234` | Reception |
| `physician` | `demo1234` | Clinic Staff |
| `dr.santos` | `demo1234` | Clinic Staff (attending physician) |
| `labtech` | `demo1234` | Laboratory |
| `biomedical` | `demo1234` | Biomedical |

---

## 3. Technology Stack

| Layer | Technology |
|-------|------------|
| Language | PHP 8.2+ |
| Framework | Laravel 11 |
| Base application | Snipe-IT (AGPL-3.0) |
| Database (default) | MySQL / MariaDB |
| Database (optional clinical) | PostgreSQL (dual-database mode) |
| Web server | Apache 2.4 (with `mod_rewrite`) |
| Frontend | Blade templates, Bootstrap, jQuery, Chart.js |
| Theme | Custom AHOP CSS (`public/css/ahop-theme.css`) |
| Cloud hosting | Google Cloud Platform (Compute Engine VM) |
| Version control | Git / GitHub |

---

## 4. System Architecture

### 4.1 Single-database mode (current deployment)

```
┌─────────────────────────────────────────────────────────┐
│                    Web Browser                         │
└────────────────────────┬────────────────────────────────┘
                         │ HTTP
┌────────────────────────▼────────────────────────────────┐
│  Apache → PHP 8.x → Laravel (AHOP / Snipe-IT)           │
│  Document root: /public                                  │
└────────────────────────┬────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────┐
│  MySQL / MariaDB                                         │
│  • users, roles, permissions, settings                   │
│  • assets, consumables (medical equipment)               │
│  • patients, opd_visits, appointments                    │
│  • lab_orders, lab_results                               │
│  • billing_invoices, billing_payments                    │
└─────────────────────────────────────────────────────────┘
```

### 4.2 Dual-database mode (optional)

When `AHOP_CLINICAL_DATABASE_ENABLED=true`:

- **MySQL** — Snipe-IT core (users, assets, RBAC)
- **PostgreSQL** — Clinical tables (patients, OPD, lab, billing)

Bridge: `app/Support/ClinicalDatabase.php`, trait `UsesClinicalDatabase` on clinical models.

### 4.3 Request flow (example: OPD visit)

```
Reception check-in → Patient record → OPD visit created →
Physician documents encounter → Lab order (optional) →
OPD completed → Auto-billing (if enabled) → Invoice & payment
```

---

## 5. Source Code Structure

### 5.1 Repository layout

```
ahop-snipe-it/
├── app/
│   ├── Console/Commands/          # AHOP artisan commands (seed, backup, setup)
│   ├── Http/Controllers/          # Clinical controllers
│   │   ├── PatientsController.php
│   │   ├── OpdVisitsController.php
│   │   ├── AppointmentsController.php
│   │   ├── LabOrdersController.php
│   │   ├── BillingInvoicesController.php
│   │   ├── ReceptionCheckInController.php
│   │   ├── ClinicalAnalyticsController.php
│   │   ├── ClinicalReportsController.php
│   │   └── Api/LabOrdersController.php
│   ├── Models/                    # Clinical Eloquent models
│   │   ├── Patient.php
│   │   ├── OpdVisit.php
│   │   ├── Appointment.php
│   │   ├── LabOrder.php
│   │   ├── LabResult.php
│   │   ├── BillingInvoice.php
│   │   └── ClinicalModel.php
│   ├── Policies/                  # Authorization policies
│   ├── Services/                  # Business logic
│   │   ├── OpdVisitInvoiceService.php
│   │   ├── AppointmentReminderService.php
│   │   ├── PhysicianSelectService.php
│   │   └── Ahop/ClinicalDashboardService.php
│   └── Support/
│       └── ClinicalDatabase.php   # Dual-DB connection resolver
├── config/
│   ├── ahop.php                   # AHOP feature configuration
│   └── ahop_roles.php             # Role permission templates
├── database/
│   ├── migrations/                # MySQL migrations (incl. AHOP columns)
│   ├── migrations/clinical/       # Clinical schema migrations
│   └── seeders/                   # Demo data seeders
├── docs/                          # This documentation package
├── public/
│   └── css/ahop-theme.css         # AgilityCare UI theme
├── resources/
│   ├── views/                     # Blade templates (clinical UI)
│   └── lang/en-US/                # Language strings (ahop.php)
├── routes/
│   ├── web.php                    # Clinical web routes
│   └── api.php                    # REST API (incl. lab integration)
└── .env.example                   # Environment template
```

### 5.2 Key design patterns

| Pattern | Usage |
|---------|--------|
| MVC | Laravel controllers, models, Blade views |
| Service classes | Billing automation, reminders, dashboard metrics |
| Policies | Per-module authorization (`PatientPolicy`, `OpdVisitPolicy`, etc.) |
| Traits | `UsesClinicalDatabase` for connection routing |
| Seeders | Idempotent demo data (`ahop:seed-all`) |
| Scheduled tasks | Backups, appointment reminders, equipment alerts |

---

## 6. Clinical Modules

### 6.1 Patients (TR5 — centralized patient data)

- **Controller:** `PatientsController`
- **Model:** `Patient`
- **Routes:** `/patients`, `/patients/{id}/clinical-summary`
- **Features:** Registration, demographics, allergies, problem list, company/clinic site

### 6.2 Appointments (TR2)

- **Controller:** `AppointmentsController`
- **Model:** `Appointment`
- **Routes:** `/appointments`, `/appointments/calendar`
- **Features:** Schedule, calendar view, check-in, email reminders (`ahop:send-appointment-reminders`)

### 6.3 Reception check-in

- **Controller:** `ReceptionCheckInController`
- **Route:** `/reception/check-in`
- **Features:** Walk-in registration, appointment check-in, patient search

### 6.4 OPD / EMR-lite (TR1)

- **Controller:** `OpdVisitsController`
- **Model:** `OpdVisit`
- **Routes:** `/opd-visits`, `/opd-visits/queue`
- **Features:** Visit documentation, vitals, assessment, diagnosis, medical certificate, lab order creation

### 6.5 Laboratory (TR3)

- **Controller:** `LabOrdersController`, `Api\LabOrdersController`
- **Models:** `LabOrder`, `LabResult`
- **Routes:** `/lab-orders`, `/api/v1/lab/orders`
- **Workflow:** ordered → in_progress → completed

### 6.6 Billing (TR4)

- **Controller:** `BillingInvoicesController`
- **Models:** `BillingInvoice`, `BillingLineItem`, `BillingPayment`, `BillableService`
- **Routes:** `/billing-invoices`
- **Features:** Invoices, line items, payments, receipts, auto-bill on OPD complete

### 6.7 Medical equipment (TR6, TR7)

- **Base:** Snipe-IT Assets, Consumables, Maintenances modules
- **Seeder:** `MedicalEquipmentSeeder`
- **Features:** Equipment registry, maintenance alerts, dashboard equipment status

### 6.8 Dashboard & reports (TR12)

- **Controller:** `DashboardController`, `ClinicalReportsController`, `ClinicalAnalyticsController`
- **Views:** `dashboard-ahop.blade.php`
- **Features:** Real-time stats (60s refresh), clinical CSV reports, analytics charts

---

## 7. Database Design

### 7.1 Clinical tables (MySQL or PostgreSQL)

| Table | Description |
|-------|-------------|
| `patients` | Patient demographics and clinical summary fields |
| `appointments` | Scheduled visits, reminder tracking |
| `opd_visits` | Outpatient encounters, vitals, documentation |
| `lab_orders` | Laboratory test orders |
| `lab_results` | Result values and flags |
| `billable_services` | Service fee catalog |
| `billing_invoices` | Patient invoices |
| `billing_line_items` | Invoice line items |
| `billing_payments` | Payment records |

### 7.2 Snipe-IT core tables (MySQL)

| Table | Description |
|-------|-------------|
| `users` | Staff accounts |
| `permission_groups` | AHOP role groups |
| `assets` | Medical equipment |
| `consumables` | Supplies |
| `companies` | Clinic / site (Company field) |
| `settings` | Application settings |
| `action_logs` | Audit trail |

### 7.3 Migration paths

```bash
# Core + AHOP MySQL migrations
php artisan migrate --force

# Clinical schema (patients, OPD, lab)
php artisan migrate --path=database/migrations/clinical --force

# Optional PostgreSQL setup
php artisan ahop:clinical-db-setup
```

---

## 8. Security & Access Control

### 8.1 RBAC (TR8)

- Role templates: `config/ahop_roles.php`
- Setup command: `php artisan ahop:setup-priority1`
- Permissions: `config/permissions.php` (patients.*, opd_visits.*, lab_orders.*, billing_invoices.*)
- Policies enforce access per module

### 8.2 Authentication

- Session-based login (`/login`)
- Username + password (bcrypt hashed)
- Optional: LDAP, SAML, Google OAuth (Snipe-IT built-in)

### 8.3 Audit logging (TR9)

- Model change history via Snipe-IT `Loggable` trait → `action_logs` table
- Dedicated BHC-style audit UI not included; logs available in admin action logs

### 8.4 Transport security

- Production: HTTPS recommended (Let's Encrypt or load balancer)
- Demo deployment: HTTP with `APP_FORCE_TLS=false`

### 8.5 Backup (TR10)

- Command: `php artisan ahop:backup`
- Schedule: daily via `php artisan schedule:run`
- Health check: `php artisan ahop:backup-health`

---

## 9. API & Integration

### 9.1 Laboratory REST API (TR13 — partial)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/lab/orders` | List lab orders |
| GET | `/api/v1/lab/orders/{id}` | Order detail |
| POST | `/api/v1/lab/orders/{id}/results` | Submit results |

Authentication: Snipe-IT API tokens (Bearer).

### 9.2 Dashboard JSON

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/ahop/dashboard-data` | Clinical dashboard refresh payload |

---

## 10. Artisan Commands

| Command | Purpose |
|---------|---------|
| `ahop:seed-all` | Full demo: roles, users, equipment, clinical data |
| `ahop:seed-demo-users` | Demo staff accounts |
| `ahop:seed-physicians` | Attending physician roster |
| `ahop:seed-equipment` | Medical equipment taxonomy |
| `ahop:seed-demo` | Patients, OPD, appointments, lab, billing |
| `ahop:setup-priority1` | RBAC groups, backup checklist |
| `ahop:clinical-db-setup` | PostgreSQL clinical database |
| `ahop:backup` | Full backup (MySQL + uploads + optional PostgreSQL) |
| `ahop:send-appointment-reminders` | Email reminders (scheduled) |
| `snipeit:create-admin` | Create superuser account |

---

## 11. Configuration Reference

Key entries in `.env` (see `.env.example` for full list):

```env
AHOP_CLINICAL_SIDEBAR=true
AHOP_THEME_ENABLED=true
AHOP_CLINICAL_DASHBOARD=true
AHOP_AUTO_BILL_OPD_COMPLETE=true
AHOP_CLINICAL_DATABASE_ENABLED=false
AHOP_DAILY_BACKUP=true
AHOP_APPOINTMENT_REMINDERS=true
APP_URL=http://35.247.142.84
```

Feature flags map to `config/ahop.php`.

---

## 12. Deployment Summary

| Environment | URL | Database |
|-------------|-----|----------|
| Local (XAMPP) | http://localhost/snipe-it/public | MySQL `snipeit` |
| Google Cloud | http://35.247.142.84 | MariaDB `snipeit` |
| Source code | https://github.com/jhewhome/ahop-snipe-it | — |

**Deploy workflow:** Local edit → `git push` → Cloud `git pull` → `composer install` → `migrate` → `config:cache`

See [AHOP-DEPLOYMENT-GUIDE.md](AHOP-DEPLOYMENT-GUIDE.md) for step-by-step instructions.

---

## 13. Requirements Traceability

| TR | Requirement | AHOP implementation | Status |
|----|-------------|---------------------|--------|
| TR1 | EMR | OPD visits, patient EMR fields, medical certificate | Partial (clinic-scoped) |
| TR2 | Appointments | Appointments module, calendar, check-in | Implemented |
| TR3 | Laboratory | Lab orders, results, API | Implemented |
| TR4 | Billing | Invoices, payments, auto-bill | Implemented |
| TR5 | Centralized database | MySQL clinical schema | Implemented |
| TR6 | Inventory | Snipe-IT consumables/assets | Implemented |
| TR7 | Equipment monitoring | Asset maintenance, alerts | Implemented |
| TR8 | RBAC | AHOP role groups, policies | Implemented |
| TR9 | Audit logging | Snipe-IT action_logs | Partial |
| TR10 | Backup / DR | `ahop:backup`, scheduled | Partial (local backup) |
| TR11 | Appointment confirmation | Email reminders | Partial (email only) |
| TR12 | Dashboard | Clinical dashboard, reports | Implemented |
| TR13 | Integration layer | Lab REST API | Partial |

---

## 14. Source Code Inventory

### 14.1 AHOP-specific files (custom development)

**Configuration**
- `config/ahop.php`
- `config/ahop_roles.php`

**Controllers (clinical)**
- `app/Http/Controllers/PatientsController.php` (extended)
- `app/Http/Controllers/OpdVisitsController.php`
- `app/Http/Controllers/AppointmentsController.php`
- `app/Http/Controllers/LabOrdersController.php`
- `app/Http/Controllers/BillingInvoicesController.php`
- `app/Http/Controllers/ReceptionCheckInController.php`
- `app/Http/Controllers/ClinicalAnalyticsController.php`
- `app/Http/Controllers/ClinicalReportsController.php`
- `app/Http/Controllers/StaffGuideController.php`
- `app/Http/Controllers/Api/LabOrdersController.php`

**Models**
- `app/Models/Patient.php`
- `app/Models/OpdVisit.php`
- `app/Models/Appointment.php`
- `app/Models/LabOrder.php`
- `app/Models/LabResult.php`
- `app/Models/BillingInvoice.php`
- `app/Models/BillingLineItem.php`
- `app/Models/BillingPayment.php`
- `app/Models/BillableService.php`
- `app/Models/ClinicalModel.php`

**Services**
- `app/Services/OpdVisitInvoiceService.php`
- `app/Services/AppointmentInvoiceService.php`
- `app/Services/AppointmentReminderService.php`
- `app/Services/AppointmentCheckInService.php`
- `app/Services/PhysicianSelectService.php`
- `app/Services/ClinicSiteService.php`
- `app/Services/Ahop/ClinicalDashboardService.php`
- `app/Services/Ahop/ClinicalReportService.php`
- `app/Services/Ahop/EquipmentAlertService.php`

**Console commands**
- `app/Console/Commands/SeedAhopAll.php`
- `app/Console/Commands/SeedAhopDemo.php`
- `app/Console/Commands/SeedAhopDemoUsers.php`
- `app/Console/Commands/SeedAhopPhysicians.php`
- `app/Console/Commands/SeedMedicalEquipment.php`
- `app/Console/Commands/SetupClinicalDatabase.php`
- `app/Console/Commands/SetupPriority1.php`
- `app/Console/Commands/AhopBackup.php`

**Database**
- `database/migrations/clinical/*.php`
- `database/migrations/2026_05_*_ahop_*.php`
- `database/seeders/Ahop*.php`
- `database/seeders/MedicalEquipmentSeeder.php`

**Views & assets**
- `resources/views/dashboard-ahop.blade.php`
- `resources/views/patients/`, `opd_visits/`, `appointments/`, `lab_orders/`, `billing_invoices/`, `reception/`
- `resources/views/partials/ahop-*.blade.php`
- `resources/views/staff-guide/`
- `public/css/ahop-theme.css`
- `resources/lang/en-US/ahop.php`

### 14.2 Inherited from Snipe-IT (not modified for thesis inventory)

Standard Snipe-IT modules remain available when clinical sidebar mode is off: licenses, accessories, components, kits, depreciation, LDAP, etc.

---

## Document history

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | June 2026 | Initial thesis documentation package |

---

*End of document — AgilityCare Health Operations Platform (AHOP)*
