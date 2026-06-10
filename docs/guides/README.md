# VibeMetrics Documentation

**Version:** 1.0.0 · **Last updated:** June 2026 · **License:** MIT

Privacy-first web analytics built with Laravel 12, Inertia, and Vue 3.

---

## Documentation index

| # | Guide | Audience | Description |
|---|-------|----------|-------------|
| 1 | [Overview](./01-overview.md) | Everyone | Product intro, privacy model, tech stack, roles |
| 2 | [User Guide](./02-user-guide.md) | Site owners | Registration, sites, dashboard, tracking snippet |
| 3 | [Admin Guide](./03-admin-guide.md) | Platform admins | Users, settings, branding, health, logs |
| 4 | [API & Analytics](./04-api-and-analytics.md) | Developers | Collect API, data pipeline, rollup, analytics engine |
| 5 | [Technical Reference](./05-technical-reference.md) | Developers | Architecture, database schema, file structure |
| 6 | [Configuration](./06-configuration.md) | DevOps / admins | `.env` variables and platform settings |
| 7 | [Development](./07-development.md) | Developers | Local setup, XAMPP, testing |
| 8 | [Deployment & Operations](./08-deployment-operations.md) | DevOps | Production deploy, servers, CI/CD, troubleshooting |
| 9 | [CI/CD with GitHub Actions](./09-cicd-github-actions.md) | DevOps | GitHub Actions step-by-step, hosting patterns, edge cases |

### Deployment-specific guides

| Guide | Environment |
|-------|-------------|
| [Hostinger Shared Hosting](../deployment/HOSTINGER-SHARED-CICD.md) | Shared hosting + GitHub Actions |
| [VPS / Cloud](../deployment/VPS-CLOUD-CICD.md) | Nginx, Supervisor, full stack |

---

## Quick reference

| Task | Command / URL |
|------|---------------|
| Start dev environment | `composer run dev` |
| Run tests | `composer test` |
| Health check | `GET /up` |
| Collect API | `POST /api/collect` |
| Admin panel | `/admin` |
| Create site | `/sites/create` |
| Platform settings | `/admin/settings` |
| System health | `/admin/health` |
| Queue worker | `php artisan queue:work` |
| Scheduler | `* * * * * php artisan schedule:run` |
| Daily rollup | `php artisan analytics:rollup` |
| Purge old data | `php artisan analytics:purge` |
| Clear caches | `php artisan optimize:clear` |
| Production optimize | `php artisan config:cache && route:cache && view:cache` |

---

## Export to PDF

Merge all guides with [Pandoc](https://pandoc.org/):

```bash
pandoc docs/guides/*.md -o docs/VIBEMETRICS-FULL-GUIDE.pdf
```

Or open any `.md` file in Word / VS Code and export individually.

---

> The original single-file guide lives at [`../VIBEMETRICS-COMPLETE-GUIDE.md`](../VIBEMETRICS-COMPLETE-GUIDE.md) and now redirects here.
