# 8. Deployment & Operations

[← Development](./07-development.md) · [Documentation index](./README.md)

---

## Production Checklist

1. **Document root** → `public/` (not project root)
2. **Configure `.env`** — see [Configuration](./06-configuration.md)
3. **Install dependencies:**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```
4. **Database:**
   ```bash
   php artisan migrate --force
   php artisan storage:link
   ```
5. **Permissions:**
   ```bash
   chmod -R ug+rwx storage bootstrap/cache
   ```
6. **Optimize:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
7. **Queue worker** — Supervisor or cron (below)
8. **Scheduler cron** — every minute
9. **Verify:** `https://your-domain.com/up` and Admin → Health

### Never expose in web root

`.env` · `vendor/` · `node_modules/` · `storage/` (except public symlink) · `database/`

---

## Background Jobs

### Scheduler

Add to crontab (every minute):

```cron
* * * * * cd /path/to/vibemetrics && php artisan schedule:run >> /dev/null 2>&1
```

| Command | Schedule | Purpose |
|---------|----------|---------|
| `analytics:rollup` | Daily 01:00 | Aggregate yesterday's stats |
| `analytics:purge` | Weekly | Delete expired raw page views |

### Queue worker

**Required in production** — page views are queued before insert.

**VPS (Supervisor):**

```ini
[program:vibemetrics-worker]
command=php /var/www/vibemetrics/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=deploy
stdout_logfile=/var/www/vibemetrics/storage/logs/worker.log
```

**Shared hosting (cron every minute):**

```cron
* * * * * php /path/to/artisan queue:work database --stop-when-empty --max-time=55
```

### Failed jobs

```bash
php artisan queue:failed
php artisan queue:retry all
```

---

## Server-Specific Guides

### XAMPP / Apache

| Item | Setting |
|------|---------|
| Document root | `.../vibemetrics/public` |
| Rewrite | `mod_rewrite` + `.htaccess` |
| Queue | `php artisan queue:work` in terminal |
| Scheduler | Windows Task Scheduler |

### Hostinger shared hosting

- No npm on server → build in CI or locally, upload `public/build/`
- No Supervisor → queue via cron every minute
- **Full guide:** [HOSTINGER-SHARED-CICD.md](../deployment/HOSTINGER-SHARED-CICD.md)

```cron
* * * * * /usr/bin/php /home/USER/domains/yourdomain.com/vibemetrics/artisan schedule:run >> /dev/null 2>&1
* * * * * /usr/bin/php /home/USER/domains/yourdomain.com/vibemetrics/artisan queue:work database --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

### Linux VPS / Cloud

- Nginx + PHP-FPM + MySQL + Supervisor + Certbot
- **Full guide:** [VPS-CLOUD-CICD.md](../deployment/VPS-CLOUD-CICD.md)
- Optional Redis for cache/queue at higher traffic

### cPanel / Plesk

Same principles as Hostinger: `public/` docroot, PHP 8.2+, MySQL, cron for scheduler + queue, upload `public/build/`.

---

## CI/CD & GitHub Actions

| File | Trigger | Purpose |
|------|---------|---------|
| `.github/workflows/ci.yml` | Push/PR to `master` | Test, build, deploy |

Workflow steps: PHP 8.3 + Node 20 → install → build → test → deploy via SSH (on push to `master`).

### GitHub Secrets

| Secret | Example |
|--------|---------|
| `SSH_HOST` | `xxx.hostinger.com` |
| `SSH_USER` | `u123456789` |
| `SSH_PORT` | `65002` |
| `SSH_KEY` | Private deploy key PEM |
| `DEPLOY_PATH` | `/home/u.../vibemetrics` |

### Deploy scripts

| Script | Environment |
|--------|-------------|
| `scripts/deploy-hostinger.sh` | Shared hosting |
| `scripts/deploy-vps.sh` | VPS |

### Manual deploy

```bash
php artisan down
git pull origin master
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart
php artisan up
# Shared hosting: build locally and upload public/build/
```

---

## Health Monitoring

| URL | Purpose |
|-----|---------|
| `/up` | Laravel boot check (HTTP 200) |
| Admin → Health | Full diagnostics |

### Monitor externally

- `/up` availability
- SSL certificate expiry
- Disk space
- Queue backlog (`jobs` table)
- Failed jobs count
- Scheduler freshness (`last_rollup_at`, `last_purge_at`)
- Error rate in `storage/logs/laravel.log`

---

## Security Checklist

- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` generated and secret
- [ ] `.env` not in git
- [ ] HTTPS + `SESSION_SECURE_COOKIE=true`
- [ ] `SESSION_ENCRYPT=true`
- [ ] Strong DB and SMTP passwords
- [ ] `TRUSTED_PROXIES` when behind CDN
- [ ] Document root is `public/` only
- [ ] Do not run `db:seed` in production
- [ ] Queue driver `database` or `redis` (not `sync`)

### Built-in security

CSRF (except `/api/collect`) · rate limits on auth and collect · email verification · password breach check · bot filtering · domain enforcement · admin self-protection · signed verification URLs

---

## Troubleshooting

| Problem | Cause | Fix |
|---------|-------|-----|
| White page / no styles | Missing `public/build/` | `npm run build`, upload build |
| No analytics | Queue not running | Start worker or add cron |
| Events missing | Paused site / wrong ID | Check site settings |
| 404 on collect | Invalid/paused tracking ID | Unpause or regenerate ID |
| Silent collect fail | Domain mismatch | URL host must match site domain |
| 503 on collect | Maintenance mode | Disable in Admin → Settings |
| 429 on collect | Rate limit | Increase limit or investigate traffic |
| Scheduler degraded | No cron | Add `schedule:run` cron |
| Rollup overdue | Cron off / rollup disabled | Fix cron, enable rollup |
| Queue degraded (sync) | Wrong driver | `QUEUE_CONNECTION=database` |
| 500 errors | Permissions / config | Check `storage/logs/laravel.log` |
| Geo always `XX` | Private IP | Enable `ANALYTICS_TRUST_GEO_HEADERS` behind CDN |
| npm not found (shared) | Expected | Build in CI or locally |
| Email not sending | SMTP config | Check `.env`, Admin → Health |

```bash
php artisan optimize:clear
php artisan config:show queue
php artisan analytics:rollup --date=2026-06-01
tail -f storage/logs/laravel.log
```

---

## Export Documentation to PDF

```bash
# All guides merged (requires Pandoc)
pandoc docs/guides/*.md -o docs/VIBEMETRICS-FULL-GUIDE.pdf

# Single guide
pandoc docs/guides/02-user-guide.md -o docs/user-guide.pdf
```

Without Pandoc: open any `.md` in Word or VS Code → Save as PDF.

---

## Related guides

- [Configuration](./06-configuration.md) — `.env` variables
- [Admin Guide](./03-admin-guide.md) — health panel and settings
- [Hostinger deployment](../deployment/HOSTINGER-SHARED-CICD.md)
- [VPS deployment](../deployment/VPS-CLOUD-CICD.md)
