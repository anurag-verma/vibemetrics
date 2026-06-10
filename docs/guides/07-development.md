# 7. Development

[← Configuration](./06-configuration.md) · [Documentation index](./README.md) · [Next: Deployment & Operations →](./08-deployment-operations.md)

---

## Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+ and npm
- SQLite (default) or MySQL

---

## Quick Start

```bash
cd vibemetrics
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build

# Optional: demo data
php artisan db:seed
```

---

## Development Server (all-in-one)

```bash
composer run dev
```

Runs concurrently:

| Process | Purpose |
|---------|---------|
| `php artisan serve` | HTTP server |
| `php artisan queue:listen` | Queue worker |
| `php artisan pail` | Log tailing |
| `npm run dev` | Vite HMR |

---

## Seeded Accounts (local only)

**Never use in production.**

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@vibemetrics.test | password |
| User | demo@vibemetrics.test | password |

The demo user has a site (`example.com`) with 14 days of sample page views.

---

## Creating an Admin Manually

```bash
php artisan tinker
>>> $u = User::where('email', 'you@example.com')->first();
>>> $u->is_admin = true;
>>> $u->save();
```

---

## XAMPP (Windows)

1. Place project in `htdocs/vibemetrics`
2. Point virtual host document root to `vibemetrics/public`
3. Enable `mod_rewrite` in Apache
4. Set `AllowOverride All` for the site directory
5. `public/.htaccess` handles URL rewriting
6. Run queue worker in a separate terminal: `php artisan queue:work`
7. Optional: Windows Task Scheduler for `php artisan schedule:run` every minute

---

## Testing

```bash
composer test
# or
php artisan test
```

### Coverage areas

- Authentication (registration, login, verification, password reset)
- Site CRUD and validation
- Site limits per user
- Admin settings and branding
- Collect API validation
- Analytics date ranges
- Health checks
- HTTP error pages
- Transactional emails
- URL normalization, bot detection, domain matching (unit tests)

CI runs the full suite on every push and pull request to `master` (see [CI/CD with GitHub Actions](./09-cicd-github-actions.md)).

---

## Useful Dev Commands

```bash
php artisan optimize:clear          # Clear all caches
php artisan config:show app         # Inspect config
php artisan analytics:rollup --date=2026-06-01
php artisan queue:failed
php artisan pail                    # Tail logs
```

---

## Related guides

- [Configuration](./06-configuration.md) — `.env` reference
- [API & Analytics](./04-api-and-analytics.md) — how tracking works
- [Deployment & Operations](./08-deployment-operations.md) — going to production
