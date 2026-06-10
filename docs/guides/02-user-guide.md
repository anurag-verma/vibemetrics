# 2. User Guide

[← Overview](./01-overview.md) · [Documentation index](./README.md) · [Next: Admin Guide →](./03-admin-guide.md)

---

## Registration & Onboarding

```
Visit /register
  → Fill name, email, timezone, password
  → Account created (if registration_enabled)
  → Redirect to email verification notice
  → User clicks verification link
  → Welcome email sent (if enabled by admin)
  → User lands on /getting-started (onboarding)
  → Create first site OR redirect to dashboard if sites exist
```

**Password rules (production):** Minimum 8 characters, letters, mixed case, numbers, and not in known breach databases.

**Rate limits:** Registration and password reset: 5 attempts per minute.

---

## Public Pages (no login required)

| URL | Page |
|-----|------|
| `/` | Landing |
| `/features` | Features |
| `/use-cases` | Use cases index |
| `/use-cases/{slug}` | Detail: `saas`, `ecommerce`, `blogs`, `agencies` |
| `/pricing` | Pricing |
| `/docs` | Public integration docs |
| `/privacy` | Privacy Policy |
| `/terms` | Terms of Service |
| `/login`, `/register` | Authentication |
| `/up` | Health check |

---

## Authenticated Routes

Requires verified email (`auth` + `verified` middleware).

| Feature | URL | Description |
|---------|-----|-------------|
| Getting started | `/getting-started` | Onboarding when you have no sites |
| Documentation | `/documentation` | In-app integration docs |
| Dashboard | `/dashboard` | Redirects to your latest site |
| Site analytics | `/sites/{site}` | Main analytics dashboard |
| Export CSV | `/sites/{site}/export` | Download analytics (10/min limit) |
| Sites list | `/sites` | Manage all sites |
| Create site | `/sites/create` | Add new site |
| Site settings | `/sites/{site}/edit` | Snippet, pause, regenerate, reset |
| Profile | `/profile` | Account settings |

---

## Creating & Tracking a Site

1. Go to **Sites → Add Site**.
2. Enter **site name** and **domain** (e.g. `myblog.com` — no `https://` needed).
3. A UUID `tracking_id` is generated automatically.
4. On **Site Settings**, copy the tracking snippet:

```html
<script defer
  data-website-id="YOUR_TRACKING_ID"
  data-api-host="https://your-vibemetrics-domain.com"
  src="https://your-vibemetrics-domain.com/js/tracker.js">
</script>
```

5. Paste before `</head>` on every page you want to track.
6. Data appears in the dashboard within seconds (queue worker must be running on the server).

### Site management actions

| Action | Effect |
|--------|--------|
| Edit name/domain | Updates site metadata |
| Pause tracking | Stops collection, hides snippet |
| Regenerate tracking ID | New UUID; old snippet stops working |
| Reset statistics | Deletes all analytics for the site |
| Delete site | Removes site and all data permanently |

### Site limits

Non-admin users are limited to `max_sites_per_user` (default **2**). Admins have unlimited sites.

---

## Analytics Dashboard

### Summary metrics

- Total page views and unique visitors
- Pages per visitor, average views per day
- % change vs prior equivalent period
- Today vs yesterday views
- **Live visitors** (last 5 minutes)

### Charts & breakdowns

- **Trend charts** — hourly (short ranges) or daily (longer ranges)
- **Traffic heatmap** — day-of-week × hour-of-day
- **Top pages**, referrers, channels, campaigns
- **UTM** sources and mediums
- Browsers, operating systems, devices, countries

### Date range presets

- Today · Last 24 hours
- This week · Last 7 days
- This month · Last 30 days · Last 90 days
- This year · Last 6 months · Last 12 months
- Custom range (up to 365 days)

Your **timezone** (Profile → Preferences) affects how "today" and week/month boundaries are calculated.

### User preferences

| Setting | Location | Effect |
|---------|----------|--------|
| Timezone | Profile → Preferences | Dashboard date boundaries |
| Default date range | Profile → Preferences | Pre-selected range on open |

### CSV export

From the dashboard, export analytics as CSV. Includes summary stats, trends, and all breakdown tables. Limited to 10 exports per minute.

---

## Embedding the Tracker

| Attribute | Required | Description |
|-----------|----------|-------------|
| `data-website-id` | Yes | UUID from Site Settings |
| `data-api-host` | Yes | Base URL of your VibeMetrics install (no trailing slash) |

### SPA support

Works automatically with React Router, Vue Router, and similar:

- `history.pushState` / `replaceState`
- Browser back/forward (`popstate`)
- Hash-based routing (`hashchange`)

### Troubleshooting tracking

| Issue | Check |
|-------|-------|
| No data | Queue worker running? Site paused? |
| 404 on collect | Valid tracking ID? Site not paused? |
| Silent failure | Page URL host must match registered domain |
| 503 | Admin may have enabled maintenance mode |

See [API & Analytics](./04-api-and-analytics.md) for technical details.

---

## Related guides

- [Admin Guide](./03-admin-guide.md) — if you are a platform administrator
- [API & Analytics](./04-api-and-analytics.md) — collect API reference
- [Development](./07-development.md) — running locally
