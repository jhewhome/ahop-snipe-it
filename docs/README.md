# AgilityCare Health Operations Platform (AHOP) — Documentation Index

**Repository:** https://github.com/jhewhome/ahop-snipe-it  
**Base platform:** Snipe-IT (Laravel 11) — customized for clinical operations  
**Production demo:** http://35.247.142.84  
**Local development:** http://localhost/snipe-it/public  

---

## Documents in this folder

| Document | Purpose |
|----------|---------|
| [AHOP-SOURCE-CODE-AND-DOCUMENTATION.md](AHOP-SOURCE-CODE-AND-DOCUMENTATION.md) | **Main thesis deliverable** — system overview, architecture, source code structure, database, deployment, RBAC, commands |
| [AHOP-USER-GUIDE.md](AHOP-USER-GUIDE.md) | End-user and staff workflow guide |
| [AHOP-DEPLOYMENT-GUIDE.md](AHOP-DEPLOYMENT-GUIDE.md) | Local setup, Google Cloud deploy, Git sync |

---

## In-application documentation

| Location | Description |
|----------|-------------|
| `/staff-guide` (when logged in) | Built-in Staff Guide with roles and workflows |
| `.env.example` | Environment variable reference |
| `config/ahop.php` | AHOP feature flags and settings |

---

## Quick links for evaluators

1. Clone: `git clone https://github.com/jhewhome/ahop-snipe-it.git`
2. Configure `.env` from `.env.example`
3. Run: `composer install`, `php artisan migrate`, `php artisan ahop:seed-all`
4. Login: username `clinicadmin`, password `demo1234` (after seeding)

---

*AgilityCare Health Operations Platform — Source Code & Documentation Package*
