# 5. Technical Reference

[← API & Analytics](./04-api-and-analytics.md) · [Documentation index](./README.md) · [Next: Configuration →](./06-configuration.md)

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        Visitor's Website                         │
│  <script src="https://your-app.com/js/tracker.js" ...>          │
└────────────────────────────┬────────────────────────────────────┘
                             │ POST /api/collect (JSON)
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                     VibeMetrics Application                      │
│  ┌──────────────┐   ┌─────────────┐   ┌──────────────────────┐ │
│  │ Collect API  │──▶│ Queue Job   │──▶│ page_views table     │ │
│  │ (throttled)  │   │ RecordPage  │   │ (raw events)         │ │
│  └──────────────┘   │ View        │   └──────────┬───────────┘ │
│                     └─────────────┘              │              │
│  ┌──────────────┐   ┌─────────────┐              │              │
│  │ Scheduler    │──▶│ analytics:  │──────────────┘              │
│  │ (cron)       │   │ rollup      │──▶ daily_stats table        │
│  │              │──▶│ purge       │──▶ deletes old page_views   │
│  └──────────────┘   └─────────────┘                             │
│  ┌──────────────┐   ┌─────────────┐                             │
│  │ Inertia/Vue  │◀──│ Dashboard   │◀── SiteAnalyticsService      │
│  │ Dashboard UI │   │ Controllers │                             │
│  └──────────────┘   └─────────────┘                             │
└─────────────────────────────────────────────────────────────────┘
```

### Dashboard request flow

1. User opens `/sites/{id}`.
2. `DashboardController` resolves date range (timezone + preferences).
3. `SiteAnalyticsService` aggregates from `page_views` and `daily_stats` (30s cache).
4. Vue dashboard renders charts and tables.

---

## Database Schema

### `users`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | string | Display name |
| email | string | Unique, verified |
| timezone | string(64) | Default `UTC` |
| default_date_range | string | Preferred analytics preset |
| password | string | Bcrypt hashed |
| is_admin | boolean | Administrator flag |
| is_active | boolean | Account active flag |
| email_verified_at | timestamp | Required for access |
| remember_token | string | Remember-me sessions |

### `sites`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Owner (FK, cascade delete) |
| name | string | Friendly name |
| domain | string | e.g. `example.com` |
| tracking_id | uuid | Used in tracking snippet |
| is_paused | boolean | Stops collection when true |

**Constraints:** Unique `(user_id, domain)`. Index on `domain`.

### `page_views`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| site_id | bigint | FK → sites |
| visitor_id | string(36) | Session UUID (nullable) |
| url | string(2048) | Normalized URL |
| referrer | string(2048) | Nullable |
| browser, os | string(50) | Parsed from User-Agent |
| device | string(20) | desktop, mobile, tablet |
| country | char(2) | ISO code (`XX` = unknown) |
| utm_source … utm_content | string | Campaign tags |
| created_at | timestamp | Event time |

**Indexes:** `(site_id, created_at)`, `(site_id, visitor_id, created_at)`, `(site_id, utm_campaign)`.

### `daily_stats`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| site_id | bigint | FK → sites |
| date | date | Aggregation date |
| page_views | int | Total views |
| unique_visitors | int | Distinct fingerprints |
| devices, top_browsers, top_os, countries | JSON | Breakdowns |
| top_urls, top_referrers, top_campaigns | JSON | Top 10 lists |
| utm_sources, utm_mediums | JSON | UTM breakdowns |

### `platform_settings`

| Column | Type | Description |
|--------|------|-------------|
| key | string | Primary key |
| value | text | JSON-encoded |
| updated_at | timestamp | |

### Infrastructure tables

`sessions` · `cache` / `cache_locks` · `jobs` / `failed_jobs` · `password_reset_tokens`

---

## Key Application Components

| Component | Path | Role |
|-----------|------|------|
| CollectController | `app/Http/Controllers/Api/` | Ingest API |
| RecordPageView | `app/Jobs/` | Async event write |
| SiteAnalyticsService | `app/Services/` | Dashboard metrics |
| PlatformSettingsService | `app/Services/` | Runtime config |
| SystemHealthService | `app/Services/` | Admin health checks |
| BotDetector | `app/Services/` | Filter crawlers |
| GeoIpResolver | `app/Services/` | Country resolution |
| SiteDomainMatcher | `app/Services/` | Domain enforcement |
| AggregateDailyStats | `app/Console/Commands/` | `analytics:rollup` |
| PurgeOldPageViews | `app/Console/Commands/` | `analytics:purge` |

---

## Project File Structure

```
vibemetrics/
├── app/
│   ├── Console/Commands/       # analytics:rollup, analytics:purge
│   ├── Http/Controllers/     # Web, Admin, API, Auth
│   ├── Http/Middleware/      # Admin, active user, Inertia
│   ├── Http/Requests/        # Form validation
│   ├── Jobs/                 # RecordPageView
│   ├── Listeners/            # Welcome & password emails
│   ├── Models/               # User, Site, PageView, DailyStat, PlatformSetting
│   ├── Policies/             # SitePolicy
│   ├── Providers/            # Rate limits, events
│   ├── Services/             # Analytics, branding, geo, health
│   └── Support/              # Date ranges, SQL, timezones
├── bootstrap/app.php         # Middleware, routing, exceptions
├── config/                   # app, analytics, database, mail
├── database/migrations/      # Schema
├── database/seeders/         # Demo data
├── docs/guides/              # This documentation set
├── public/
│   ├── index.php             # Entry point
│   └── js/tracker.js         # Analytics tracker
├── resources/js/             # Vue pages and components
├── routes/
│   ├── web.php               # Web routes
│   ├── api.php               # Collect API
│   ├── auth.php              # Authentication
│   └── console.php           # Scheduled commands
├── scripts/                  # Deploy scripts
├── storage/                  # Logs, cache, uploads
└── tests/                    # Feature and unit tests
```

---

## Related guides

- [API & Analytics](./04-api-and-analytics.md) — collect API and data pipeline
- [Configuration](./06-configuration.md) — environment variables
- [Development](./07-development.md) — local setup
