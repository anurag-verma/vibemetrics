# 1. Overview

[← Documentation index](./README.md) · [Next: User Guide →](./02-user-guide.md)

---

## Introduction

**VibeMetrics** is a privacy-first web analytics platform. It lets website owners track page views, referrers, devices, countries, and UTM campaign data **without cookies** and **without storing visitor IP addresses**.

The application is built as a self-hosted SaaS-style product:

- **End users** register, add websites (sites), embed a lightweight JavaScript tracker, and view analytics dashboards.
- **Administrators** manage the platform: users, global settings, branding, system health, and logs.

VibeMetrics is designed to be deployed on your own infrastructure (local XAMPP, shared hosting like Hostinger, or a Linux VPS).

---

## Key Features & Privacy Model

### What VibeMetrics tracks

| Data point | Source | Stored? |
|------------|--------|---------|
| Page URL | Browser `location.href` | Yes (normalized, max 2048 chars) |
| Referrer | `document.referrer` | Yes (optional) |
| Device type | User-agent inference (desktop/mobile/tablet) | Yes |
| Browser & OS | Parsed server-side from User-Agent | Yes |
| Country | CDN/proxy headers or GeoIP lookup | Yes (2-letter code, e.g. `US`) |
| UTM parameters | URL query string | Yes (source, medium, campaign, term, content) |
| Visitor ID | `sessionStorage` UUID (per browser tab session) | Yes (for unique visitor counts) |

### What VibeMetrics does **not** track

- **No cookies** for analytics (visitor ID uses `sessionStorage` only)
- **No IP address storage** in the database
- **No personal identification** (names, emails of visitors)
- **Bot traffic filtered** — known bot/crawler user-agents are silently dropped

### Product capabilities

- Real-time-ish **live visitor** count (active in last 5 minutes)
- **Traffic heatmap** (day-of-week × hour-of-day)
- **Date range presets** with timezone support
- **CSV export** of analytics data
- **Daily rollups** for historical performance
- **Automatic data purge** based on retention policy
- **Site pause** — stop collection without deleting the site
- **Domain enforcement** — reject events from URLs that don't match the registered domain
- **Rate limiting** on the collect API (per IP and per site)
- **Platform branding** — custom logo, favicon, primary color, display name
- **Transactional emails** — welcome, password changed, account deactivated
- **Admin health dashboard** — database, cache, queue, scheduler, storage, mail, ingest checks

---

## Technology Stack

| Layer | Technology |
|-------|------------|
| Backend framework | Laravel 12 (PHP 8.2+) |
| Frontend SPA | Vue 3 + Inertia.js 2 |
| Build tool | Vite 7 |
| CSS | Tailwind CSS 3/4 |
| Charts | Chart.js + vue-chartjs |
| Auth | Laravel Breeze (session-based) |
| Email verification | Laravel `MustVerifyEmail` |
| Queue | Laravel database queue (Redis optional) |
| Cache | Database cache (Redis optional) |
| Database | SQLite (dev), MySQL/MariaDB or PostgreSQL (production) |
| HTTP client (tracker) | `fetch` + `sendBeacon` (vanilla JS, no dependencies) |

### PHP extensions required

`mbstring`, `pdo`, `xml`, `curl`, `zip`, `bcmath`, `intl` (recommended)

---

## User Roles & Permissions

### Regular user

- Register (if enabled), verify email, log in
- Create sites up to `max_sites_per_user` limit (admins are unlimited)
- View/edit/delete own sites only
- View analytics dashboard, export CSV
- Edit profile (name, email, timezone, default date range, password)
- Cannot access `/admin/*` routes

### Administrator (`is_admin = true`)

- All regular user capabilities plus unlimited sites
- Access to Admin panel — see [Admin Guide](./03-admin-guide.md)
- Redirected to `/admin` after login (instead of `/dashboard`)
- Cannot remove own admin access or deactivate own account

### Account states

| State | Behavior |
|-------|----------|
| Unverified email | Blocked from app routes |
| `is_active = false` | Cannot log in |
| Site `is_paused = true` | Tracking snippet hidden; collect API rejects events |

---

## Related guides

- [User Guide](./02-user-guide.md) — day-to-day usage
- [Admin Guide](./03-admin-guide.md) — platform management
- [Technical Reference](./05-technical-reference.md) — architecture and database
- [Deployment & Operations](./08-deployment-operations.md) — going live
