# 3. Admin Guide

[← User Guide](./02-user-guide.md) · [Documentation index](./README.md) · [Next: API & Analytics →](./04-api-and-analytics.md)

---

All routes under `/admin` require authentication, verified email, and the `admin` role.

After login, administrators are redirected to `/admin` instead of `/dashboard`.

---

## Admin Dashboard (`/admin`)

- Platform-wide metrics: users, sites, page views, events
- User registration trend chart
- Status indicators: registration open/closed, maintenance mode, rollup status
- Date range filtering

---

## User Management (`/admin/users`)

Paginated list with site counts per user.

| Action | Effect |
|--------|--------|
| Toggle **admin** | Grant or revoke administrator access |
| Toggle **active** | Deactivate account (blocks login; email sent if enabled) |

**Protections:**

- Cannot demote or deactivate your own account
- Cannot delete the last remaining admin

---

## Site Management (`/admin/sites`)

- View all sites across all users
- Toggle **paused** on any site (stops event collection)

---

## Platform Settings (`/admin/settings`)

Settings are stored in the database and override `.env` defaults where applicable. See also [Configuration](./06-configuration.md).

### Limits & data

| Setting | Default | Range | Description |
|---------|---------|-------|-------------|
| `max_sites_per_user` | 2 | 1–100 | Site cap for non-admin users |
| `retention_days` | 365 | 30–3650 | Raw page view retention |
| `rollup_enabled` | true | boolean | Daily aggregation on/off |
| `collect_rate_limit` | 120 | 10–1000 | Collect API requests per IP/minute |
| `default_date_range` | last_30_days | preset | Default analytics range for new users |

### Platform control

| Setting | Default | Description |
|---------|---------|-------------|
| `registration_enabled` | true | Allow new sign-ups |
| `maintenance_mode` | false | Block collect API (returns 503) |

### Branding

| Setting | Default | Description |
|---------|---------|-------------|
| `app_display_name` | VibeMetrics | Shown in UI and emails |
| `support_email` | null | Support contact |
| `brand_primary_color` | #4f46e5 | Theme accent color |
| `site_logo_path` | null | Uploaded site logo |
| `email_logo_path` | null | Email header logo |
| `favicon_path` | null | Browser favicon |
| `email_logo_same_as_site` | true | Reuse site logo in emails |

Upload logos and favicon via **Admin → Settings**. Files are stored in `storage/app/public/branding/` (requires `php artisan storage:link`).

### Email toggles

| Setting | Default | Description |
|---------|---------|-------------|
| `transactional_emails_enabled` | true | Master email switch |
| `email_welcome_enabled` | true | After email verification |
| `email_password_changed_enabled` | true | After password reset |
| `email_account_deactivated_enabled` | true | When admin deactivates user |

### Auto-managed timestamps

| Key | Updated by |
|-----|------------|
| `last_rollup_at` | `analytics:rollup` command |
| `last_purge_at` | `analytics:purge` command |

---

## System Health (`/admin/health`)

Snapshot refreshed every 30 seconds.

| Check | Monitors |
|-------|----------|
| Server | CPU %, RAM %, load average, uptime |
| Database | Connection, latency, size |
| Cache | Read/write probe, latency |
| Queue | Pending/failed jobs; warns if `sync` in production |
| Storage | Disk usage, log file size |
| Scheduler | Last rollup/purge vs expected schedule |
| OPcache | Enabled status, hit rate |
| Mail | Driver, from address |
| Ingest | Maintenance mode, rate limit, events in 24h |

Also shows app version, PHP/Laravel versions, and table row counts.

**Status levels:** `healthy` · `degraded` · `unhealthy`

---

## Log Viewer (`/admin/logs`)

- Lists `storage/logs/laravel*.log` files
- Tail view with level filtering (ERROR, WARNING, etc.)
- Max 2000 lines per request

---

## Email & Branding

### Email events

| Event | When |
|-------|------|
| Welcome email | User verifies email address |
| Password changed | User completes password reset |
| Account deactivated | Admin deactivates a user |

All emails respect the platform email toggles and use branding from `BrandingService`.

### Branding assets

| Asset | Use |
|-------|-----|
| Site logo | App navigation |
| Email logo | Email templates |
| Favicon | Browser tab icon |

---

## Admin Profile (`/admin/profile`)

Same capabilities as the user profile (name, email, password, timezone) within the admin layout.

---

## Related guides

- [Configuration](./06-configuration.md) — full `.env` reference
- [Deployment & Operations](./08-deployment-operations.md) — production checklist, troubleshooting
- [API & Analytics](./04-api-and-analytics.md) — rollup and purge commands
