# MAHA E-SEVA ERP — PRODUCTION DEPLOYMENT GUIDE

This document provides the standard operational procedure for deploying the **Maha E-Seva ERP Management System** to a production Linux/Windows server environment.

---

## 1. System Requirements

| Component | Minimum Specification | Recommended |
| :--- | :--- | :--- |
| **Operating System** | Ubuntu 22.04 LTS / Debian 12 / RHEL 9 | Ubuntu 24.04 LTS |
| **PHP Version** | PHP 8.2+ or PHP 8.3+ | PHP 8.2 / 8.3 CLI & FPM |
| **Database** | MySQL 8.0+ / MariaDB 10.4+ | MySQL 8.0 / MariaDB 10.11 LTS |
| **Web Server** | Nginx 1.22+ or Apache 2.4+ | Nginx with HTTP/2 & TLS 1.3 |
| **Process Manager** | Supervisor / systemd | Supervisor |
| **Memory** | 2 GB RAM | 4 GB+ RAM |

### Required PHP Extensions:
- `php-bcmath` (Precision financial calculations)
- `php-ctype`
- `php-curl` (External SMS/WhatsApp/Payment gateways)
- `php-dom` / `php-xml`
- `php-fileinfo` (MIME-type detection for document vault)
- `php-filter`
- `php-hash` (SHA-256 OTP and checksum verification)
- `php-json` (Dynamic service form fields)
- `php-mbstring`
- `php-openssl`
- `php-pdo`
- `php-pdo_mysql` (MySQL database connectivity)
- `php-session`
- `php-tokenizer`
- `php-zip` (CSV export compression)

---

## 2. Directory & Web Root Configuration

### **CRITICAL SECURITY RULE:**
The web server document root **MUST** be set strictly to the `/public` subdirectory of the project.

```
/var/www/maha-eseva/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/                 <--- WEB SERVER ROOT (Only this directory is exposed)
│   ├── index.php
│   └── css/
├── storage/
│   ├── app/
│   │   ├── private/
│   │   │   └── documents/  <--- PROTECTED VAULT (Strictly non-accessible via web)
│   │   └── public/         <--- Only this is symlinked by storage:link
│   ├── framework/
│   └── logs/
└── .env                    <--- PROTECTED CONFIGURATION (Outside web root)
```

---

## 3. Web Server Virtual Host Setup

### Nginx Configuration Example (`/etc/nginx/sites-available/maha-eseva.conf`):

```nginx
server {
    listen 80;
    server_name erp.mahaeseva.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name erp.mahaeseva.com;

    # Document Root (Strictly /public)
    root /var/www/maha-eseva/public;
    index index.php index.html;

    # SSL Certificates
    ssl_certificate /etc/letsencrypt/live/erp.mahaeseva.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/erp.mahaeseva.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

    # Block access to hidden files (.env, .git)
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Route all requests through index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM Socket
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Static assets caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }

    client_max_body_size 25M;
}
```

---

## 4. File Permissions

Grant write permissions exclusively to `storage` and `bootstrap/cache`:

```bash
cd /var/www/maha-eseva

# Assign ownership to web server user (e.g. www-data)
sudo chown -R www-data:www-data /var/www/maha-eseva

# Set standard directory and file permissions
sudo find /var/www/maha-eseva -type d -exec chmod 755 {} \;
sudo find /var/www/maha-eseva -type f -exec chmod 644 {} \;

# Grant write access to storage and cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 5. Production Deployment Step-by-Step

### Step 1: Clone Repository & Configure Environment
```bash
git clone <production-repo-url> /var/www/maha-eseva
cd /var/www/maha-eseva

# Copy environment template
cp .env.example .env

# Edit .env with production database and domain settings
nano .env
```

**Production `.env` checklist:**
```ini
APP_NAME="MAHA E-SEVA ERP"
APP_ENV=production
APP_KEY=base64:... # Generated via php artisan key:generate
APP_DEBUG=false
APP_URL=https://erp.mahaeseva.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=maha_eseva_prod
DB_USERNAME=maha_eseva_user
DB_PASSWORD="<STRONG_RANDOM_PASSWORD>"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=local
```

### Step 2: Install Optimized Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### Step 3: Generate Application Key
```bash
php artisan key:generate --force
```

### Step 4: Run Safe Production Database Migrations
> **WARNING**: NEVER run `php artisan migrate:fresh` in production.
```bash
php artisan migrate --force
```

### Step 5: Establish Public Asset Symlink
```bash
php artisan storage:link
```

### Step 6: Cache Configuration, Routes, and Views
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 6. Background Queue Worker (Supervisor)

The system uses queues for asynchronous SMS/WhatsApp dispatch, background reports, and document processing.

Create `/etc/supervisor/conf.d/maha-eseva-worker.conf`:

```ini
[program:maha-eseva-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/maha-eseva/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/maha-eseva/storage/logs/worker.log
stopwaitsecs=3600
```

Start Supervisor worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start maha-eseva-worker:*
```

---

## 7. Automated Cron / Scheduler

Configure the Laravel scheduler to run every minute via crontab:

```bash
sudo crontab -u www-data -e
```

Add the following line:
```cron
* * * * * cd /var/www/maha-eseva && php artisan schedule:run >> /dev/null 2>&1
```

**Automated Jobs Handled by Scheduler:**
- Pruning expired OTP records (Daily)
- Cleaning failed queue jobs older than 7 days (Weekly)
- Clearing expired password reset tokens (Daily)

---

## 8. Backup & Disaster Recovery Procedure

### 1. Database Backup (Automated Daily Cron):
```bash
# Automated Daily MySQL Dump
mysqldump -u maha_eseva_user -p'<PASSWORD>' --single-transaction --quick --routines --triggers maha_eseva_prod | gzip > /backups/db_backup_$(date +\%F_\%T).sql.gz
```

### 2. Private Document Vault Backup:
```bash
# Backup encrypted customer document storage
tar -czf /backups/vault_backup_$(date +\%F_\%T).tar.gz -C /var/www/maha-eseva/storage/app/private documents
```

### 3. Offsite Backup Synchronization:
- Sync `/backups/` directory daily to secure encrypted cloud storage (e.g. AWS S3 Glacier or secure SFTP backup server).

---

## 9. Maintenance Mode & Zero-Downtime Updates

### Enable Maintenance Mode (with bypass secret):
```bash
php artisan down --secret="maha-ops-secret-2026" --render="errors::503"
# You can bypass maintenance mode via: https://erp.mahaeseva.com/maha-ops-secret-2026
```

### Standard Application Update Script:
```bash
cd /var/www/maha-eseva
php artisan down

git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo supervisorctl restart maha-eseva-worker:*

php artisan up
```

### Rollback Procedure (If Migration / Deployment Fails):
```bash
php artisan migrate:rollback --step=1 --force
git checkout <previous-stable-commit-hash>
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```
