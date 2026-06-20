# AgilityCare Health Operations Platform (AHOP) — Documentation

**Repository:** https://github.com/jhewhome/ahop-snipe-it  
**Base platform:** Snipe-IT (Laravel 11) — customized for clinical operations  
**Live demo:** https://ahop.jhewhome.xyz  
**Local development:** http://localhost/snipe-it/public  

---

## Documentation (PDF)

| Document | Description |
|----------|-------------|
| [AHOP-SOURCE-CODE-AND-DOCUMENTATION.pdf](AHOP-SOURCE-CODE-AND-DOCUMENTATION.pdf) | **Main thesis deliverable** — architecture, source code, database, RBAC, deployment |
| [AHOP-USER-GUIDE.pdf](AHOP-USER-GUIDE.pdf) | Staff and end-user workflow guide |
| [AHOP-DEPLOYMENT-GUIDE.pdf](AHOP-DEPLOYMENT-GUIDE.pdf) | Local setup, Google Cloud deploy, Git sync |
| [AHOP-DEFENSE-FAQ.pdf](AHOP-DEFENSE-FAQ.pdf) | **Thesis defense** — one-page FAQ: demo script, AI scope, panel Q&A |

---

## Markdown sources (edit and re-export to PDF if needed)

| Document | Description |
|----------|-------------|
| [AHOP-DEMO-SCRIPT.md](AHOP-DEMO-SCRIPT.md) | **8-minute class demo** — scripted presentation for masteral subject instructor |

## Live demo (for evaluators)

1. Open: https://ahop.jhewhome.xyz/login  
2. **Username:** `clinicadmin`  
3. **Password:** `demo1234`  

Other demo accounts (`reception`, `physician`, `labtech`) use the same password after seeding.

---

## Quick setup (from source code)

```bash
git clone https://github.com/jhewhome/ahop-snipe-it.git
cd ahop-snipe-it
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan ahop:seed-all --password=demo1234
```

---

## In-application help

| Location | Description |
|----------|-------------|
| `/staff-guide` | Built-in Staff Guide (when logged in) |
| `.env.example` | Environment variable reference |

---

*AgilityCare Health Operations Platform — Source Code & Documentation*
