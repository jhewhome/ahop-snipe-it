# AHOP User Guide

## AgilityCare Health Operations Platform — Staff & Evaluator Guide

**Login URL (cloud):** http://35.247.142.84/login  
**Login URL (local):** http://localhost/snipe-it/public/login  

After demo seeding, use **username** (not email) and password **`demo1234`**.

---

## 1. Getting started

### 1.1 Log in

1. Open the login page.
2. Enter your **username** (e.g. `reception`, `physician`, `clinicadmin`).
3. Enter your password.
4. Click **Sign in**.

### 1.2 Built-in Staff Guide

After login, open **Staff Guide** from the sidebar (if enabled). It describes roles, policies, and recommended workflows.

---

## 2. Role-based workflows

### 2.1 Reception (`reception`)

**Typical day:**

1. **Reception → Check-In** — Register walk-in patients or check in scheduled appointments.
2. **Patients** — Search, register, or update patient demographics.
3. **Appointments** — Schedule follow-up visits; use calendar view.
4. **OPD → Queue** — Confirm patient appears in queue after check-in.

### 2.2 Clinic Staff / Physician (`physician`, `dr.santos`, etc.)

1. **OPD → Queue** — Select patient from queue.
2. Open visit — Record chief complaint, vitals, assessment, diagnosis.
3. Create **lab order** from visit if needed.
4. Mark visit **completed** (may auto-create billing invoice).
5. Print **medical certificate** when required.

### 2.3 Laboratory (`labtech`)

1. **Lab Orders** — View pending orders.
2. Open order — Enter results (test name, value, unit, flag).
3. Status moves to **in progress** then **completed**.

### 2.4 Clinic Administrator (`clinicadmin`)

1. **Dashboard** — Review daily operations (patients, OPD, lab, billing).
2. **Billing** — Issue invoices, record payments, print receipts.
3. **Reports → Clinical** — Export operational reports (CSV).
4. **Clinical Analytics** — Review trends and equipment metrics.
5. **Admin → Users** — Assign staff to AHOP role groups.

### 2.5 Biomedical (`biomedical`)

1. **Medical Equipment → List All** — View asset registry.
2. **Maintenances** — Schedule and track equipment maintenance.
3. Review equipment alerts on dashboard.

---

## 3. Module reference

### Patients

| Action | Navigation |
|--------|------------|
| List patients | Sidebar → Patients |
| Register patient | Patients → Create |
| Clinical summary | Patient profile → Clinical summary |
| Company / clinic site | Set on patient form (Company field) |

### Appointments

| Action | Navigation |
|--------|------------|
| List | Sidebar → Appointments |
| Calendar | Appointments → Calendar |
| Send reminder | Appointment detail → Send reminder (requires patient email) |

### OPD visits

| Action | Navigation |
|--------|------------|
| Queue | Sidebar → OPD → Queue |
| New visit | OPD → Create |
| Lab from visit | Visit page → Create lab order |
| Medical certificate | Visit page → Medical certificate |

### Billing

| Action | Navigation |
|--------|------------|
| Invoices | Sidebar → Billing |
| Add payment | Invoice → Record payment |
| Receipt | Invoice → Print receipt |

---

## 4. Demo data

After running `php artisan ahop:seed-all`, the system includes:

- 10 demo patients (e.g. Maria Santos Dela Cruz)
- Sample OPD visits, appointments, lab orders, invoices
- Medical equipment categories and sample assets
- Demo staff accounts (see main documentation)

Demo patients are prefixed with patient numbers like `AC-900001` and may be deleted in test environments.

---

## 5. Attending physician

OPD and appointment forms include an **Attending Physician** dropdown populated from users in:

- AHOP Clinic Staff
- AHOP Clinic Administrator

Seed physicians: `php artisan ahop:seed-physicians`

---

## 6. Tips

- Use **Ctrl+F5** after UI updates to refresh cached CSS.
- **Company** field on patients represents the **clinic site** (not employer).
- Stop using Excel or paper logs for new patients once AHOP is live (per Staff Guide policy).
- For password resets, contact clinic administrator or IT superuser.

---

## 7. Support & documentation

| Resource | Location |
|----------|----------|
| Technical documentation | `docs/AHOP-SOURCE-CODE-AND-DOCUMENTATION.md` |
| Deployment guide | `docs/AHOP-DEPLOYMENT-GUIDE.md` |
| Source code | https://github.com/jhewhome/ahop-snipe-it |
| In-app Staff Guide | `/staff-guide` |

---

*AgilityCare Health Operations Platform — User Guide v1.0*
