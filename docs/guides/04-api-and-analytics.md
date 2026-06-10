# 4. API & Analytics

[← Admin Guide](./03-admin-guide.md) · [Documentation index](./README.md) · [Next: Technical Reference →](./05-technical-reference.md)

---

## Collect API

**Endpoint:** `POST /api/collect`  
**Content-Type:** `application/json`  
**Authentication:** None (identified by `tracking_id`)  
**CSRF:** Exempt · **CORS:** Enabled for cross-origin requests

### Request body

```json
{
  "tracking_id": "550e8400-e29b-41d4-a716-446655440000",
  "url": "https://example.com/blog/post",
  "referrer": "https://google.com/",
  "device": "desktop",
  "visitor_id": "6ba7b810-9dad-11d1-80b4-00c04fd430c8",
  "utm_source": "newsletter",
  "utm_medium": "email",
  "utm_campaign": "june_launch"
}
```

| Field | Required | Validation |
|-------|----------|------------|
| tracking_id | Yes | Valid UUID |
| url | Yes | Valid URL, max 2048 chars |
| referrer | No | String, max 2048 |
| visitor_id | No | UUID |
| device | No | `desktop`, `mobile`, or `tablet` |
| utm_* | No | String, max 100 chars each |

### Responses

| Status | Meaning |
|--------|---------|
| 204 | Accepted (or silently dropped for bots/domain mismatch) |
| 404 | Unknown or paused tracking ID |
| 422 | Validation error |
| 429 | Rate limit exceeded |
| 503 | Maintenance mode enabled |

### Rate limiting

Configured via `collect_rate_limit` (default 120/min):

- **Per IP:** `collect_rate_limit` requests/minute
- **Per tracking ID:** 5× that limit (max 5000)

---

## Event Collection Pipeline

### Client (`public/js/tracker.js`)

1. Reads `data-website-id` and `data-api-host` from the script tag.
2. Generates or reuses `visitor_id` in `sessionStorage` (key: `vm_visitor_id`).
3. Infers device from User-Agent.
4. Parses UTM params from the current URL.
5. Deduplicates — won't re-send for the same URL in one session.
6. Sends via `sendBeacon` (same-origin) or `fetch` with `keepalive` (cross-origin).

### Server (`CollectController`)

1. **Maintenance mode** → HTTP 503
2. **Bot detection** → HTTP 204 (silent drop)
3. **Site lookup** by `tracking_id` (cached 5 min) → 404 if missing or paused
4. **Domain enforcement** → 204 if URL host doesn't match site domain (subdomains allowed)
5. **GeoIP** → country from CDN headers or ip-api.com (IP never stored)
6. **Dispatch** `RecordPageView` job → HTTP 204

### Job (`RecordPageView`)

- Parses User-Agent for browser/OS
- Normalizes URL
- Inserts row into `page_views`

---

## Analytics Aggregation (Rollup)

| | |
|---|---|
| **Command** | `php artisan analytics:rollup` |
| **Schedule** | Daily at 01:00 server time |
| **Manual** | `php artisan analytics:rollup --date=2026-06-01` |

For each site, for yesterday (or specified date):

- Count page views and unique visitors
- Build ranked JSON: devices, browsers, OS, countries, URLs, referrers, campaigns, UTM
- Upsert into `daily_stats`
- Record `last_rollup_at` in platform settings

Disable via Admin → Settings (`rollup_enabled`).

---

## Data Retention & Purge

| | |
|---|---|
| **Command** | `php artisan analytics:purge` |
| **Schedule** | Weekly |

- Deletes `page_views` older than `retention_days` (default 365) in batches of 10,000
- Records `last_purge_at`
- `daily_stats` are **not** purged automatically

---

## Analytics Engine

### Data sources

| Time range | Source |
|------------|--------|
| Today (partial) | Raw `page_views` |
| Historical days | `daily_stats` + today's raw data |
| Short ranges (today, 24h) | Hourly granularity |
| Longer ranges | Daily granularity |

### Unique visitors

Based on `visitor_id` from sessionStorage when available. SQL logic in `AnalyticsSql::visitorFingerprintExpression()`.

### Live visitors

Distinct visitors with events in the **last 5 minutes**.

### Caching

| Data | TTL |
|------|-----|
| Site analytics | 30 seconds |
| Site by tracking ID | 5 minutes |
| Platform settings | 5 minutes |
| GeoIP lookups | 24 hours |
| Admin health | 30 seconds |

### CSV export

`GET /sites/{site}/export?range=...`

Includes summary, trends, top pages, referrers, channels, campaigns, UTM, browsers, OS, devices, countries. Throttled to 10/minute per user.

---

## Related guides

- [Technical Reference](./05-technical-reference.md) — database tables
- [Admin Guide](./03-admin-guide.md) — rollup and retention settings
- [Deployment & Operations](./08-deployment-operations.md) — queue worker and scheduler
