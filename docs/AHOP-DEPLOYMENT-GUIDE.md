# AHOP Deployment Guide

## 1. Local development (XAMPP / Windows)

### Requirements

- PHP 8.2+
- Composer
- MySQL (MariaDB)
- Apache with `mod_rewrite`

### Steps

```bash
cd C:\PUP\htdocs\snipe-it
composer install
copy .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
APP_URL=http://localhost/snipe-it/public
DB_DATABASE=snipeit
DB_USERNAME=root
DB_PASSWORD=
AHOP_CLINICAL_SIDEBAR=true
AHOP_CLINICAL_DATABASE_ENABLED=false
```

```bash
php artisan migrate --force
php artisan migrate --path=database/migrations/clinical --force
php artisan ahop:seed-all --password=demo1234
```

Open: http://localhost/snipe-it/public

---

## 2. Google Cloud Platform (production demo)

### Infrastructure

| Component | Detail |
|-----------|--------|
| Provider | Google Cloud Platform |
| VM | Ubuntu 24.04 LTS |
| Web server | Apache 2.4 |
| Database | MariaDB (MySQL compatible) |
| PHP | 8.3 |
| Public URL | http://35.247.142.84 |

### Server stack install

```bash
sudo apt update
sudo apt install -y apache2 mariadb-server php8.3-fpm php8.3-mysql php8.3-xml \
  php8.3-mbstring php8.3-curl php8.3-gd php8.3-zip php8.3-bcmath php8.3-intl \
  composer git unzip
```

### Database

```bash
sudo mysql -e "CREATE DATABASE snipeit; CREATE USER 'snipeit'@'localhost' IDENTIFIED BY 'PASSWORD'; GRANT ALL ON snipeit.* TO 'snipeit'@'localhost'; FLUSH PRIVILEGES;"
```

### Clone from GitHub

```bash
cd /var/www/html
sudo git clone https://github.com/jhewhome/ahop-snipe-it.git snipe-it
cd snipe-it
cp .env.example .env
nano .env   # set APP_URL, DB_*, AHOP_* flags
composer install --no-dev --optimize-autoloader --no-scripts
```

### Apache virtual host

`DocumentRoot` must be `/var/www/html/snipe-it/public`

```bash
sudo a2enmod rewrite
sudo systemctl reload apache2
```

### Deploy commands

```bash
php artisan key:generate
php artisan migrate --force
php artisan migrate --path=database/migrations/clinical --force
php artisan ahop:seed-all --password=demo1234
php artisan config:cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Scheduler (backups & appointment reminders)

```bash
sudo crontab -u www-data -e
```

```
* * * * * cd /var/www/html/snipe-it && php artisan schedule:run >> /dev/null 2>&1
```

---

## 3. Git sync workflow (PC → GitHub → Cloud)

### On development PC

```powershell
cd C:\PUP\htdocs\snipe-it
git add .
git commit -m "Describe change"
git push origin main
```

### On Google Cloud VM

```bash
~/deploy-ahop.sh
```

Example `~/deploy-ahop.sh`:

```bash
#!/bin/bash
set -e
cd /var/www/html/snipe-it
git pull origin main
composer install --no-dev --optimize-autoloader --no-scripts
php artisan migrate --force
php artisan migrate --path=database/migrations/clinical --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo chown -R www-data:www-data storage bootstrap/cache
echo "Deploy complete."
```

---

## 4. Environment variables (AHOP)

| Variable | Recommended | Purpose |
|----------|-------------|---------|
| `AHOP_CLINICAL_SIDEBAR` | `true` | Clinical menu (patients, OPD, lab, billing) |
| `AHOP_THEME_ENABLED` | `true` | Teal AgilityCare theme |
| `AHOP_CLINICAL_DASHBOARD` | `true` | Operations dashboard |
| `AHOP_CLINICAL_DATABASE_ENABLED` | `false` | Single MySQL (local/cloud demo) |
| `AHOP_DAILY_BACKUP` | `true` | Scheduled backups |
| `APP_URL` | Full URL with `http://` | Required — no bare IP |

---

## 5. Troubleshooting

| Problem | Solution |
|---------|----------|
| 403 GitHub clone | Use Personal Access Token with `repo` scope |
| `vendor/` missing | `composer install` with write permissions |
| Redirect loop | `APP_URL=http://IP`, `APP_FORCE_TLS=false`, `SECURE_COOKIES=false` |
| Missing `patients.email` | `ALTER TABLE patients ADD COLUMN email VARCHAR(150) NULL;` |
| Permission denied SFTP | Upload to home folder, `sudo rsync` to web root |
