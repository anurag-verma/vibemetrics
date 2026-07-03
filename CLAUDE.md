# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Tech Stack

**Backend:** Laravel 12, PHP 8.2+  
**Frontend:** Vue 3, Inertia.js, Tailwind CSS  
**Build:** Vite, PostCSS  
**Database:** MySQL/PostgreSQL/SQLite (local development)  
**Queue:** Database-backed (configurable; defaults to `queue:database`)  
**Authentication:** Laravel Sanctum + session-based (Breeze scaffolding)  
**Testing:** PHPUnit with in-memory SQLite database  

## Project Purpose

VibeMetrics is a privacy-first web analytics platform built with Laravel and Vue 3. Key features:
- **Page-view tracking** with no cookies and no IP storage (only geolocation and visitor fingerprinting via session storage)
- **Multi-site support** per user (configurable limits via platform settings)
- **Admin dashboard** for platform-wide health, user management, and settings
- **Data aggregation** via scheduled tasks (daily rollups of raw page views into `daily_stats`)
- **Timezone-aware analytics** with user-configurable date range presets
- **Bot detection** via user agent pattern matching
- **Branding customization** (admin panel supports custom app name and logo)

## Core Architecture

### Data Flow: Event Collection

1. **Tracking snippet** (`public/js/tracker.js`) embeds on customer websites
2. **Client-side collection:**
   - Generates/persists session-based `visitor_id` (UUID in sessionStorage)
   - Infers device type from user agent
   - Parses UTM parameters from URL
   - Sends JSON POST to `/api/collect` (no authentication required, UUID-based tracking)

3. **API handler** (`App\Http\Controllers\Api\CollectController`):
   - Validates request via `CollectPageViewRequest` (checks tracking_id UUID format, validates URL, etc.)
   - Detects bots via `BotDetector` (simple pattern matching on user agent)
   - Resolves geolocation via `GeoIpResolver` (extracts from request headers; respects ANALYTICS_TRUST_GEO_HEADERS)
   - Validates site exists and is active (caches site lookups 5 minutes by tracking_id)
   - Optionally enforces domain matching via `SiteDomainMatcher`
   - **Dispatches job** `RecordPageView` to async queue (does NOT block response; returns 204 No Content immediately)

4. **Queue job** (`App\Jobs\RecordPageView`):
   - Parses user agent via `UserAgentParser` (regex-based; returns browser + OS)
   - Normalizes URL via `UrlNormalizer`
   - Inserts raw page view into `page_views` table with all metadata
   - Retries up to 3 times on failure (backoff: 1s, 5s, 15s)

### Data Flow: Analytics Aggregation

1. **Scheduled command** `analytics:rollup` (runs daily at 01:00 per cron entry in README)
   - Iterates all sites
   - Counts page views and unique visitors per day (uniqueness via fingerprint expression in `AnalyticsSql`)
   - Ranks devices, browsers, OS, countries, URLs, referrers, campaigns, UTM sources/mediums
   - Upserts into `daily_stats` table per site/date (avoids duplicates, idempotent)
   - Updates platform setting `last_rollup_at` timestamp

2. **SiteAnalyticsService** provides on-demand metrics:
   - Accepts `Site`, `AnalyticsDateRange`, and optional timezone
   - Caches results for 30 seconds by site ID + range + timezone
   - Blends pre-aggregated `daily_stats` (for past dates) with raw `page_views` queries (for current day)
   - Computes trends (hourly or daily based on date range granularity)
   - Calculates period-over-period percent change vs previous period
   - Fetches live visitors (unique in last minute) separately
   - Returns comprehensive analytics object consumed by frontend Dashboard

3. **Data retention** via scheduled command `analytics:purge`:
   - Deletes page views older than `retention_days` setting (default 365)
   - Configurable per admin → settings

### Database Schema

**users table:**
- Standard Laravel auth columns (id, name, email, password, email_verified_at)
- `is_admin`, `is_active` (booleans; default false for new registrations)
- `timezone` (IANA timezone string; used for analytics date boundaries)
- `default_date_range` (preset name: 'today', 'yesterday', '7d', '30d', '90d', etc.)
- `site_limit` (enforced via `SiteLimitService` during site creation)

**sites table:**
- `user_id` (foreign key; cascade delete)
- `name`, `domain` (unique per user; domain validated at registration)
- `tracking_id` (UUID; auto-generated; used as public identifier for tracking)
- `is_paused` (boolean; paused sites reject new events)
- Composite index on (user_id, domain); separate index on domain for lookups

**page_views table:**
- `site_id`, `url` (2048 chars), `referrer` (2048 chars nullable)
- `visitor_id` (UUID nullable; session-based fingerprinting)
- `browser`, `os`, `device`, `country` (2-char code)
- `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content` (all nullable, 100 chars)
- `created_at` (timestamp; no update)
- Composite index (site_id, created_at) for range queries; separate index (site_id, utm_campaign)

**daily_stats table:**
- `site_id`, `date` (unique per site/date)
- Aggregated counts: `page_views`, `unique_visitors` (integers)
- JSON arrays: `devices`, `top_browsers`, `top_os`, `countries`, `top_urls`, `top_referrers`, `top_campaigns`, `utm_sources`, `utm_mediums`
- Each JSON entry has format `{label: string, count: int}`

**platform_settings table:**
- Simple key-value store for admin-configurable settings (e.g., `maintenance_mode`, `collect_rate_limit`, `retention_days`, `rollup_enabled`, etc.)
- Accessed via `PlatformSettingsService` (wraps getInt/getBool/set logic)

**sessions table:**
- Standard Laravel session driver (configured to use database; can be switched to Redis)

### Frontend Routing & Components

**Layouts** (Inertia persistent layouts):
- `AppLayout.vue` - Authenticated user dashboard (sidebar with site list, nav)
- `AdminLayout.vue` - Admin-only dashboard (admin-specific sidebar)
- `MarketingLayout.vue` - Public marketing pages
- `GuestLayout.vue` - Auth pages (login, register, password reset)

**Pages** (Vue components serving as Inertia pages):
- `Dashboard.vue` - Main analytics view for authenticated site
  - Accepts props: `site`, `metrics` (from `SiteAnalyticsService`), `dateRange`
  - Uses `DateRangePicker` component for filtering
  - Renders metric cards, chart via `Chart.js`, rank lists (browsers, countries, etc.)
  - Auto-refreshes live visitor count and allows manual metric refresh
  - Hooks: keyboard shortcuts (see `useRelativeUpdatedLabel`)

- `Sites/Index.vue` - User's sites list
- `Sites/Create.vue` - Create new site form
- `Sites/Edit.vue` - Edit site settings (name, domain, pause/reset options)

- `Admin/Dashboard.vue` - Platform-wide stats (not implemented in detail; structure ready)
- `Admin/Users.vue` - Manage users (activate/deactivate/promote/demote/delete)
- `Admin/Sites.vue` - Global site management
- `Admin/Settings.vue` - Platform settings (rate limits, retention, branding upload, etc.)
- `Admin/Health.vue` - System health checks (database, cache, queue, storage, scheduler)
- `Admin/Logs.vue` - Streaming app logs via `LogReaderService`
- `Admin/Profile.vue` - Admin account settings

- `Landing.vue` - Public homepage
- `Marketing/Features.vue`, `Pricing.vue`, `UseCases/Show.vue`, `Docs.vue` - Marketing pages
- `Documentation.vue` - In-app docs viewer

### Key Services

**SiteAnalyticsService:**
- Core aggregation logic; produces metrics object for frontend
- Caches 30 seconds; uses pre-computed daily_stats + live page_views queries
- Handles timezone-aware date boundaries

**AnalyticsDateRange:**
- Encapsulates date range logic (presets + custom ranges)
- Provides `startUtc()`, `endUtc()`, `startLocal`, `endLocal`, `dayCount()`, `isHourlyTrend()`
- Used by frontend date picker and backend service

**BotDetector:**
- Simple string pattern matching on user agent (20+ patterns: bot, spider, googlebot, lighthouse, etc.)
- Called in `CollectController` before queuing job

**UserAgentParser:**
- Regex-based parsing of user agent string
- Returns `{browser, os}` object
- Supports Chrome, Firefox, Safari, Edge, Opera; handles mobile variants

**GeoIpResolver:**
- Extracts country code from request headers (X-Forwarded-For, CloudFlare headers, etc.)
- Respects `ANALYTICS_TRUST_GEO_HEADERS` config flag (false by default for security)

**PlatformSettingsService:**
- Wrapper around `platform_settings` table
- Methods: `getBool()`, `getInt()`, `get()`, `set()`
- Caches settings; invalidates on write

**SitePolicy:**
- Single policy class (Laravel authorization)
- Checks: user owns the site before view/update/delete

**SystemHealthService:**
- Runs system checks: database connection, cache, queue, storage, scheduler
- Returns status array consumed by admin health page

**TransactionalEmailService:**
- Sends transactional emails (welcome, password reset, etc.)
- Configured via `.env` (MAIL_* variables; defaults to Brevo SMTP relay)

**SiteLimitService:**
- Enforces per-user site creation limits
- Checks `users.site_limit` against count of active sites

**RichTextSanitizer:**
- Sanitizes branding/admin text inputs (prevents XSS)

**UrlNormalizer:**
- Normalizes URLs before storage (removes fragments, lowercases, etc.)

### API Routes

**Public routes** (no auth):
- `POST /api/collect` - Page view collection endpoint
  - Rate limited via `throttle:collect` (configurable 120 req/min per IP; 600 req/min per site UUID)
  - Returns 204 No Content on success
  - Returns 503 if maintenance mode enabled
  - Returns 404 if site not found or paused
  - Bots return 204 silently

**Authenticated routes:**
- `GET /dashboard` -> redirect to latest user site OR show dashboard
- `GET /sites` -> list user sites
- `POST /sites` -> create site
- `GET /sites/{site}` -> dashboard for specific site
- `GET /sites/{site}/export` -> CSV export (rate limited 10/min)
- `PATCH /sites/{site}` -> update site (name, domain)
- `POST /sites/{site}/regenerate-tracking-id` -> rotate tracking UUID
- `POST /sites/{site}/reset` -> purge all data for site
- `DELETE /sites/{site}` -> delete site

**Admin-only routes** (prefix `/admin`, middleware `admin`):
- `GET /admin` -> admin dashboard
- `GET /admin/health` -> health status
- `GET /admin/logs` -> log streaming
- `GET /admin/users` -> user management
- `PATCH /admin/users/{user}` -> toggle is_admin, is_active
- `DELETE /admin/users/{user}` -> delete user
- `GET /admin/sites` -> global site list
- `PATCH /admin/sites/{site}` -> pause/unpause
- `GET /admin/settings` -> settings form
- `PUT /admin/settings` -> update settings
- `POST /admin/settings/branding/{type}` -> upload logo/favicon
- `DELETE /admin/settings/branding/{type}` -> delete branding asset

## Common Commands

**Setup (fresh install):**
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan db:seed  # (optional; creates demo admin/user accounts)
```

**Development (hot reload + queue listener + log streaming):**
```bash
composer run dev
```
This spawns 4 concurrent processes:
- Laravel dev server (port 8000)
- Queue listener (database connection; 1 try, no timeout)
- Pail log viewer
- Vite dev server (HMR)

**Build assets for production:**
```bash
npm run build
```

**Run tests:**
```bash
composer test
# or
php artisan test
```

**Run single test file or method:**
```bash
php artisan test tests/Feature/CollectPageViewTest.php
php artisan test tests/Feature/CollectPageViewTest.php --filter testCollectPageView
```

**Run scheduled tasks manually (useful for testing):**
```bash
php artisan analytics:rollup               # Daily aggregation
php artisan analytics:rollup --date=2026-06-09  # Specific date
php artisan analytics:purge                # Data retention cleanup
```

**Run queue worker (production):**
```bash
php artisan queue:work --tries=3 --timeout=60
```

**Lint PHP code:**
```bash
./vendor/bin/pint  # Laravel's PHP formatter/linter
```

**Cache management:**
```bash
php artisan config:cache     # Cache config (production optimization)
php artisan route:cache      # Cache routes
php artisan view:cache       # Cache views
php artisan config:clear     # Clear caches (local development)
```

**Database:**
```bash
php artisan migrate                  # Run all pending migrations
php artisan migrate:fresh           # Drop all tables and re-run (development only)
php artisan migrate:rollback         # Undo last migration batch
php artisan tinker                   # Interactive REPL
```

## Important Patterns & Conventions

### Timezone Handling
- User model stores IANA timezone name (e.g., 'America/New_York')
- `AnalyticsDateRange` converts dates to user's local timezone
- Aggregation command `analytics:rollup` uses local date boundaries (not UTC)
- Always respect user's timezone when returning date ranges or analytics

### Caching Strategy
- Site lookups cached 5 minutes (by tracking_id) in `CollectController`
- Analytics metrics cached 30 seconds (by site + range + timezone)
- Platform settings cached (invalidated on write)
- All cache keys namespaced (e.g., `site:{tracking_id}`, `site_analytics:{id}:{range}:{tz}`)

### Rate Limiting
- Collect endpoint uses custom rate limiter: 120 req/min per IP, 600 req/min per site UUID
- Auth endpoints throttled: register 5/min, login throttled, password reset 5/min
- Export endpoint 10/min

### Job Queue
- All page view persistence happens async via `RecordPageView` job
- No database writes in HTTP request path (fire-and-forget)
- Jobs retry 3 times with backoff (1s, 5s, 15s)
- Uses database queue driver by default (can switch to Redis in `.env`)

### Authorization
- Middleware `auth` requires login; `verified` requires email verification
- Middleware `admin` checks `users.is_admin` boolean
- `SitePolicy` enforces user ownership of sites
- No explicit authorization checks in controllers; relies on middleware + policy

### Validation
- Form requests located in `app/Http/Requests/` with rule() method
- API validation in `CollectPageViewRequest` (UUID format, URL format, max lengths)
- Admin forms validated in dedicated request classes (e.g., `UpdateAdminUserRequest`)

### Inertia & Props
- Controllers return `Inertia::render('PageName', $props)` (not JSON)
- Props automatically serialized as JSON in HTML data attributes
- Frontend accesses via `defineProps()` in Vue components
- Site model automatically excluded from props; only needed properties passed

### Asset Building
- CSS: Tailwind CSS with PostCSS; compiled from `resources/css/app.css`
- JS: Vite entry at `resources/js/app.js`; resolves Vue pages from `resources/js/Pages/`
- Path alias `@/*` maps to `resources/js/*`
- Tracker script (`public/js/tracker.js`) is plain JS; not bundled by Vite

## Testing

**Test structure:**
- `tests/Unit/` - Unit tests (services, helpers, etc.)
- `tests/Feature/` - Feature tests (HTTP requests, database state)

**Database testing:**
- PHPUnit config runs tests in in-memory SQLite (`:memory:`)
- All tests run migrations fresh per test suite
- Example: `php artisan test tests/Feature/CollectPageViewTest.php`

**Key test considerations:**
- Auth tests need `->actingAs($user)` to bypass middleware
- API tests should validate response status + JSON structure
- Service tests should mock dependencies (cache, database, external APIs)

## Configuration Files

- **`.env.example`** - Template; copy to `.env` for local/production config
- **`config/app.php`** - App name, debug mode, timezone, locale
- **`config/database.php`** - Database connections (sqlite, mysql, pgsql)
- **`config/queue.php`** - Queue driver (database, redis, sync)
- **`config/cache.php`** - Cache store (database, redis, array, file)
- **`config/analytics.php`** - App-specific: `enforce_collect_domain`, `retention_days`, `rollup_enabled`
- **`tailwind.config.js`** - Custom colors (brand indigo, warm paper tones), animations
- **`vite.config.js`** - Vite plugins (Laravel, Vue), entry points
- **`phpunit.xml`** - Test configuration; defines test suites (Unit, Feature)

## Deployment Notes

- **Queue worker required:** Page views are queued; without a worker, they never persist
- **Scheduler required:** Rollup and purge tasks run via cron calling `php artisan schedule:run` every minute
- **Storage writable:** `storage/` and `bootstrap/cache/` must be writable by web server
- **Public directory:** Web server document root must point to `public/`, not project root
- **Assets cached:** Run `npm run build` and commit `public/build/` to version control (or build on deploy)
- **Maintenance mode:** Set `APP_MAINTENANCE_DRIVER=file` or `database`; paused sites return 404 from API
- **HTTPS & cookies:** Set `SESSION_SECURE_COOKIE=true` and `SESSION_ENCRYPT=true` in production

## File Organization

```
/app
  /Console/Commands          (Artisan commands: analytics:rollup, analytics:purge)
  /Http/Controllers          (Route handlers; split into Admin/, Api/, Auth/)
  /Http/Requests             (Form request validation classes)
  /Http/Middleware           (Auth, admin check, rate limiting)
  /Jobs                      (Queued jobs: RecordPageView)
  /Listeners                 (Event listeners: welcome/password reset emails)
  /Mail                      (Mailable classes)
  /Models                    (Eloquent: User, Site, PageView, DailyStat, PlatformSetting)
  /Policies                  (Authorization: SitePolicy)
  /Providers                 (Service providers; AppServiceProvider registers rate limiters)
  /Rules                     (Custom validation rules)
  /Services                  (Business logic: analytics, geo-ip, bot detection, etc.)
  /Support                   (Helpers: AnalyticsDateRange, AnalyticsSql, DateFormatter, etc.)
/routes
  /web.php                   (Web routes; uses Inertia)
  /api.php                   (API routes; only /collect endpoint)
  /auth.php                  (Auth routes; login, register, password reset)
  /console.php               (Artisan command schedule)
/resources
  /js
    /app.js                  (Inertia setup; Vue 3 entry point)
    /bootstrap.js            (Axios configuration)
    /Pages                   (Vue components for each route)
    /Layouts                 (Persistent layouts: AppLayout, AdminLayout, etc.)
    /Components              (Reusable Vue components: MetricCard, DateRangePicker, etc.)
    /Composables             (Vue 3 composables: useTheme, useRelativeUpdatedLabel, etc.)
  /css
    /app.css                 (Tailwind directives)
  /views                     (Blade templates; mainly app.blade.php for Inertia)
/database
  /migrations                (Schema changes)
  /factories                 (Model factories for testing)
  /seeders                   (Database seeders: demo data)
/public
  /.htaccess                 (Apache rewrite rules)
  /js/tracker.js             (Privacy-first analytics snippet for client websites)
  /build                     (Compiled assets; generated by Vite)
/tests
  /Feature                   (HTTP/integration tests)
  /Unit                      (Unit tests)
/docs
  /guides                    (User/admin/API documentation)
  /deployment                (CI/CD guides: Hostinger, VPS)
```

## Debugging Tips

- **Enable app logging:** Set `LOG_LEVEL=debug` in `.env`; tail `storage/logs/laravel.log`
- **Queue debugging:** Use `php artisan queue:listen --tries=1 --timeout=0` during development (prints exceptions to console)
- **Database queries:** Enable query logging in service provider; check `storage/logs/` for slow/failed queries
- **Vue component debugging:** Use Vue DevTools browser extension; Inertia payloads visible in Network tab
- **Cache issues:** Run `php artisan cache:clear` to clear all caches (careful in production)
- **Mail testing:** Set `MAIL_MAILER=log` in `.env` to log emails instead of sending; check `storage/logs/`