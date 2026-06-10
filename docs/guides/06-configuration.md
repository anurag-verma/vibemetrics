# 6. Configuration

[← Technical Reference](./05-technical-reference.md) · [Documentation index](./README.md) · [Next: Development →](./07-development.md)

---

Copy `.env.example` to `.env` and adjust values. In production, run `php artisan config:cache` after changes.

---

## Application

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_NAME` | VibeMetrics | Application name |
| `APP_VERSION` | 1.0.0 | Shown in UI and health panel |
| `APP_ENV` | local | Set `production` in prod |
| `APP_KEY` | (empty) | Run `php artisan key:generate` |
| `APP_DEBUG` | true | **Must be `false` in production** |
| `APP_URL` | http://localhost | Full base URL with scheme |

---

## Database

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_CONNECTION` | sqlite | Use `mysql` or `pgsql` in production |
| `DB_HOST` | 127.0.0.1 | Database host |
| `DB_PORT` | 3306 | Database port |
| `DB_DATABASE` | — | Database name |
| `DB_USERNAME` | — | Database user |
| `DB_PASSWORD` | — | Database password |

---

## Session & Auth

| Variable | Default | Description |
|----------|---------|-------------|
| `SESSION_DRIVER` | database | Session storage |
| `SESSION_LIFETIME` | 43200 | 30 days (minutes) |
| `AUTH_REMEMBER_DURATION` | 43200 | Remember-me duration |
| `SESSION_ENCRYPT` | false | Set `true` in production |
| `SESSION_SECURE_COOKIE` | (unset) | Set `true` with HTTPS |
| `SESSION_DOMAIN` | null | Cookie domain if needed |

---

## Queue & Cache

| Variable | Default | Description |
|----------|---------|-------------|
| `QUEUE_CONNECTION` | database | Use `redis` on high-traffic VPS |
| `CACHE_STORE` | database | Use `redis` optionally |

---

## Mail

| Variable | Default | Description |
|----------|---------|-------------|
| `MAIL_MAILER` | smtp | SMTP recommended for production |
| `MAIL_HOST` | smtp-relay.brevo.com | SMTP server |
| `MAIL_PORT` | 587 | SMTP port |
| `MAIL_USERNAME` | — | SMTP username |
| `MAIL_PASSWORD` | — | SMTP password |
| `MAIL_FROM_ADDRESS` | hello@example.com | Sender email |
| `MAIL_FROM_NAME` | ${APP_NAME} | Sender name |

---

## Analytics

| Variable | Default | Description |
|----------|---------|-------------|
| `ANALYTICS_RETENTION_DAYS` | 365 | Default raw data retention |
| `ANALYTICS_ROLLUP_ENABLED` | true | Enable daily stat aggregation |
| `ANALYTICS_TRUST_GEO_HEADERS` | false | Trust `CF-IPCountry` etc. from CDN |
| `ANALYTICS_ENFORCE_DOMAIN` | true | Reject events from wrong domains |
| `TRUSTED_PROXIES` | (unset) | `*` or comma-separated IPs behind proxy/CDN |

> Enable `ANALYTICS_TRUST_GEO_HEADERS` only when geo headers come from a CDN/proxy you control.

---

## Redis (optional)

| Variable | Default |
|----------|---------|
| `REDIS_HOST` | 127.0.0.1 |
| `REDIS_PASSWORD` | null |
| `REDIS_PORT` | 6379 |

When using Redis for queue/cache, restart Supervisor after changing drivers.

---

## Production `.env` template

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=vibemetrics
DB_USERNAME=...
DB_PASSWORD=...

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
MAIL_FROM_ADDRESS=hello@your-domain.com
```

---

## Platform settings (Admin UI)

These live in the `platform_settings` database table and are editable at **Admin → Settings**. They override `.env` defaults for runtime behavior.

See the full table in [Admin Guide → Platform Settings](./03-admin-guide.md#platform-settings-adminsettings).

---

## Related guides

- [Admin Guide](./03-admin-guide.md) — changing settings in the UI
- [Deployment & Operations](./08-deployment-operations.md) — production checklist
