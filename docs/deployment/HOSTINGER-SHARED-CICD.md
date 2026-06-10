# VibeMetrics CI/CD — Hostinger Shared Hosting

This guide covers deploying **VibeMetrics** (Laravel 12 + Inertia + Vue 3) on **Hostinger Business / shared hosting** with **GitHub Actions** CI/CD.

---

## 1. Overview

### What works on shared hosting

- SSH + `git pull`
- PHP 8.2+ and Composer
- MySQL database
- Cron jobs via hPanel
- SFTP / SCP for file uploads

### What does **not** work (or is limited)

- **No Node.js/npm** on most shared plans → build frontend in **GitHub Actions**
- **No Supervisor** → queue via **cron every minute**, not a 24/7 worker
- `public/build` is **gitignored** → must deploy built assets after every release

### CI/CD flow

```
Push to main
  → GitHub Actions: composer test + npm run build
  → SSH: git pull, composer install, migrate, cache
  → SCP: upload public/build/ to server
  → Verify Admin → Health
```

---

## 2. One-time server setup

### 2.1 Enable SSH

1. Log in to **hPanel**
2. Go to **Advanced → SSH Access**
3. Enable SSH and note:
   - **Host** (e.g. `xxx.hostinger.com`)
   - **Port** (often `65002`)
   - **Username** (e.g. `u123456789`)
4. Add your SSH public key (or a dedicated deploy key for GitHub)

Connect:

```bash
ssh -p 65002 u123456789@your-host.hostinger.com
```

### 2.2 Clone the repository

```bash
cd ~/domains/yourdomain.com
git clone https://github.com/YOUR_USER/vibemetrics.git
cd vibemetrics
```

Replace `YOUR_USER` and paths with your actual GitHub repo and Hostinger directory layout.

### 2.3 Document root (critical)

Laravel must serve from the **`public/`** directory.

1. hPanel → **Domains** → your domain → **Document root**
2. Set to: `/home/u123456789/domains/yourdomain.com/vibemetrics/public`

If document root cannot be changed, follow Hostinger’s Laravel deployment docs to map `public_html` to `public/`.

### 2.4 PHP version

hPanel → **Advanced → PHP Configuration** → select **PHP 8.2** or **8.3**.

### 2.5 Create MySQL database

hPanel → **Databases** → create database and user. Use credentials in `.env`.

### 2.6 Production `.env`

On the server only (never commit `.env`):

```bash
cp .env.example .env
nano .env
```

Minimum production values:

```env
APP_NAME=VibeMetrics
APP_VERSION=1.0.0
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=database
CACHE_STORE=database

TRUSTED_PROXIES=*
ANALYTICS_TRUST_GEO_HEADERS=true
ANALYTICS_ENFORCE_DOMAIN=true

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-smtp-user
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS=hello@yourdomain.com
```

Install and optimize:

```bash
php artisan key:generate
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
chmod -R ug+rwx storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2.7 First frontend build

The server has no npm. On your **local machine** or via GitHub Actions artifact:

```bash
npm ci
npm run build
```

Upload the entire **`public/build/`** folder to the server via File Manager or SFTP.

### 2.8 Cron jobs (hPanel → Cron Jobs)

**Scheduler** (every minute):

```cron
* * * * * /usr/bin/php /home/u123456789/domains/yourdomain.com/vibemetrics/artisan schedule:run >> /dev/null 2>&1
```

**Queue worker** (every minute — shared hosting pattern):

```cron
* * * * * /usr/bin/php /home/u123456789/domains/yourdomain.com/vibemetrics/artisan queue:work database --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

Replace the path with your real path to `artisan` (run `pwd` inside the project on SSH).

Ensure `.env` has:

```env
QUEUE_CONNECTION=database
```

---

## 3. GitHub secrets

Repository → **Settings → Secrets and variables → Actions → New repository secret**

| Secret | Description | Example |
|--------|-------------|---------|
| `SSH_HOST` | Hostinger SSH hostname | `xxx.hostinger.com` |
| `SSH_USER` | SSH username | `u123456789` |
| `SSH_PORT` | SSH port | `65002` |
| `SSH_KEY` | Private deploy key (full PEM) | Contents of `deploy_key` |
| `DEPLOY_PATH` | Absolute path to app on server | `/home/u.../domains/yourdomain.com/vibemetrics` |

### Generate a deploy key

On your computer:

```bash
ssh-keygen -t ed25519 -C "vibemetrics-hostinger-deploy" -f deploy_key -N ""
```

- Add **`deploy_key.pub`** to Hostinger → SSH Access → SSH keys
- Add **`deploy_key`** (private) to GitHub secret `SSH_KEY`

---

## 4. GitHub Actions workflows

These files live in the repository:

| File | Purpose |
|------|---------|
| `.github/workflows/ci.yml` | Run tests on push/PR |
| `.github/workflows/deploy-hostinger.yml` | Deploy to Hostinger on push to `main` |

### Enable deployment

1. Push workflows to GitHub
2. Add all secrets from section 3
3. Push to `main` → Actions tab shows CI + Deploy

### Manual deploy script (on server)

Copy `scripts/deploy-hostinger.sh` to the server, set `APP_DIR`, then:

```bash
chmod +x deploy-hostinger.sh
./deploy-hostinger.sh
```

GitHub Actions runs equivalent steps automatically.

---

## 5. Manual update (without Actions)

```bash
ssh -p 65002 u123456789@your-host.hostinger.com
cd ~/domains/yourdomain.com/vibemetrics

php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

Then on your PC:

```bash
npm ci && npm run build
```

Upload **`public/build/`** to the server.

---

## 6. Post-deploy verification

1. `https://yourdomain.com/up` returns healthy
2. **Admin → Health**:
   - Debug mode **OFF**
   - Queue not using `sync` (use `database` + cron)
   - Scheduler shows rollup activity after cron runs
3. Dashboard loads with styles (proves `public/build` is present)
4. Tracking snippet sends events to `POST /api/collect`
5. Sidebar shows **Version X.X.X** from `APP_VERSION`

---

## 7. Troubleshooting

| Problem | Solution |
|---------|----------|
| `npm: command not found` | Expected on shared hosting — build in GitHub Actions |
| White page / broken UI | Deploy `public/build/` |
| `APP_DEBUG` still ON | Set `APP_DEBUG=false`, run `php artisan config:cache` |
| Queue degraded (`sync`) | Set `QUEUE_CONNECTION=database`, add queue cron |
| Scheduler “never” | Add scheduler cron in hPanel |
| 500 error | Check `storage/logs/laravel.log`, fix `storage/` permissions |
| Collect blocked | Check `ANALYTICS_ENFORCE_DOMAIN`, URL must match site domain |

---

## 8. Security checklist

- [ ] `APP_DEBUG=false` in production
- [ ] `SESSION_SECURE_COOKIE=true` with HTTPS
- [ ] `.env` never committed to git
- [ ] Do not run `php artisan db:seed` in production
- [ ] `TRUSTED_PROXIES` set when behind Hostinger proxy/CDN
- [ ] Strong database and SMTP passwords

---

## 9. Files reference

```
.github/workflows/ci.yml
.github/workflows/deploy-hostinger.yml
scripts/deploy-hostinger.sh
docs/deployment/VPS-CLOUD-CICD.md    ← use for VPS instead
```
