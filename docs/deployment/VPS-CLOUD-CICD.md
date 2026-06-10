# VibeMetrics CI/CD — Cloud / VPS Hosting

This guide covers deploying **VibeMetrics** on a **Linux VPS** (DigitalOcean, Hetzner, AWS EC2, Linode, Vultr, etc.) with **Nginx**, **Supervisor**, and **GitHub Actions**.

---

## 1. Overview

### Advantages over shared hosting

- Full control: Nginx, PHP-FPM, MySQL, Node.js
- **Supervisor** for 24/7 queue workers
- Standard Laravel production stack
- Optional Redis for cache/queue

### CI/CD flow

```
Push to main
  → GitHub Actions: tests (CI)
  → SSH: run deploy.sh on server
  → git pull, composer, npm build, migrate, cache, queue restart
```

---

## 2. Server requirements

- Ubuntu 22.04 or 24.04 LTS (recommended)
- 1 GB RAM minimum (2 GB+ recommended)
- PHP 8.2+
- MySQL 8 or MariaDB
- Nginx
- Node.js 20 LTS
- Composer 2.x
- Supervisor
- Certbot (Let's Encrypt SSL)

---

## 3. One-time server setup

### 3.1 Install packages

```bash
sudo apt update && sudo apt upgrade -y

sudo apt install -y nginx mysql-server php8.3-fpm php8.3-mysql php8.3-mbstring \
  php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-intl unzip git curl supervisor

curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

Verify:

```bash
php -v
node -v
composer -V
```

### 3.2 Create deploy user

```bash
sudo adduser deploy
sudo usermod -aG www-data deploy
```

### 3.3 Clone application

```bash
sudo mkdir -p /var/www
sudo chown deploy:www-data /var/www
su - deploy

cd /var/www
git clone https://github.com/YOUR_USER/vibemetrics.git
cd vibemetrics
```

### 3.4 MySQL database

```bash
sudo mysql
```

```sql
CREATE DATABASE vibemetrics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'vibemetrics'@'localhost' IDENTIFIED BY 'strong-password-here';
GRANT ALL PRIVILEGES ON vibemetrics.* TO 'vibemetrics'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3.5 Production `.env`

```bash
cp .env.example .env
nano .env
```

```env
APP_NAME=VibeMetrics
APP_VERSION=1.0.0
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=vibemetrics
DB_USERNAME=vibemetrics
DB_PASSWORD=strong-password-here

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=database
CACHE_STORE=database

TRUSTED_PROXIES=*
ANALYTICS_TRUST_GEO_HEADERS=true
ANALYTICS_ENFORCE_DOMAIN=true

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=hello@yourdomain.com
```

Initial install:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3.6 Permissions

```bash
sudo chown -R deploy:www-data /var/www/vibemetrics
sudo chmod -R ug+rwx /var/www/vibemetrics/storage /var/www/vibemetrics/bootstrap/cache
```

### 3.7 Nginx configuration

Create `/etc/nginx/sites-available/vibemetrics`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/vibemetrics/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/vibemetrics /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 3.8 SSL (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### 3.9 Supervisor (queue worker)

Create `/etc/supervisor/conf.d/vibemetrics-worker.conf`:

```ini
[program:vibemetrics-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/vibemetrics/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/vibemetrics/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start vibemetrics-worker:*
```

### 3.10 Cron (scheduler)

```bash
sudo crontab -u deploy -e
```

```cron
* * * * * cd /var/www/vibemetrics && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled tasks (from `routes/console.php`):

- `analytics:rollup` — daily at 01:00
- `analytics:purge` — weekly

---

## 4. Deploy script

The repository includes `scripts/deploy-vps.sh`. On the server:

```bash
chmod +x /var/www/vibemetrics/scripts/deploy-vps.sh
```

Run manually:

```bash
cd /var/www/vibemetrics
./scripts/deploy-vps.sh
```

---

## 5. GitHub Actions

### Secrets

| Secret | Description |
|--------|-------------|
| `SSH_HOST` | Server IP or hostname |
| `SSH_USER` | `deploy` |
| `SSH_PORT` | `22` |
| `SSH_KEY` | Private deploy key PEM |
| `DEPLOY_PATH` | App root on server | `/var/www/vibemetrics` |

### Workflows

| File | Purpose |
|------|---------|
| `.github/workflows/ci.yml` | Tests on push/PR |
| `.github/workflows/deploy-vps.yml` | Deploy on push to `main` |

### Deploy key setup

```bash
ssh-keygen -t ed25519 -C "vibemetrics-vps-deploy" -f deploy_key -N ""
cat deploy_key.pub >> ~/.ssh/authorized_keys
```

Add private key to GitHub secret `SSH_KEY`.

---

## 6. Post-deploy verification

```bash
curl -I https://yourdomain.com/up
sudo supervisorctl status vibemetrics-worker:*
tail -f /var/www/vibemetrics/storage/logs/laravel.log
```

In the app:

1. **Admin → Health** — all checks healthy or expected
2. Dashboard loads with analytics
3. Tracking snippet works on a registered domain
4. Version visible in sidebar

---

## 7. Optional: Redis

For higher traffic:

```bash
sudo apt install -y redis-server
```

`.env`:

```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
```

Restart Supervisor after changing queue driver.

---

## 8. VPS vs Hostinger shared

| Item | Hostinger shared | VPS |
|------|------------------|-----|
| npm on server | No | Yes |
| Queue | Cron every minute | Supervisor 24/7 |
| Deploy assets | SCP `public/build` | `npm run build` on server |
| SSL | hPanel | Certbot |
| Guide | `HOSTINGER-SHARED-CICD.md` | This file |

---

## 9. Files reference

```
.github/workflows/ci.yml
.github/workflows/deploy-vps.yml
scripts/deploy-vps.sh
docs/deployment/HOSTINGER-SHARED-CICD.md
```
