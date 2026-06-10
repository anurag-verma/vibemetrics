# 9. CI/CD with GitHub Actions

[← Deployment & Operations](./08-deployment-operations.md) · [Documentation index](./README.md)

**Audience:** Developers deploying Laravel apps (including VibeMetrics) to shared hosting, VPS, or cloud  
**Last updated:** June 2026

---

## Table of Contents

1. [What CI/CD means](#1-what-cicd-means)
2. [GitHub Actions fundamentals](#2-github-actions-fundamentals)
3. [Laravel-specific CI/CD concerns](#3-laravel-specific-cicd-concerns)
4. [Industry standards by hosting type](#4-industry-standards-by-hosting-type)
5. [Pipeline A: With tests (recommended)](#5-pipeline-a-with-tests-recommended)
6. [Pipeline B: Without tests](#6-pipeline-b-without-tests)
7. [Shared hosting pattern (Hostinger, cPanel)](#7-shared-hosting-pattern)
8. [VPS pattern (Nginx + Supervisor)](#8-vps-pattern)
9. [Cloud and managed patterns](#9-cloud-and-managed-patterns)
10. [Secrets, environments, and branches](#10-secrets-environments-and-branches)
11. [Edge cases and failure modes](#11-edge-cases-and-failure-modes)
12. [VibeMetrics workflow explained](#12-vibemetrics-workflow-explained)
13. [Checklists](#13-checklists)

---

## 1. What CI/CD means

### CI — Continuous Integration

Every push or pull request triggers automated checks on a clean machine:

- Install dependencies (`composer install`, `npm ci`)
- Lint / static analysis (optional)
- Run tests (`php artisan test`)
- Build frontend (`npm run build`)

**Goal:** Catch broken code before it reaches production.

### CD — Continuous Delivery / Deployment

After CI passes, automatically (or with approval) deploy to a server:

- Pull code or upload artifacts
- Run migrations
- Cache config, routes, and views
- Restart queue workers
- Upload built assets when the server has no Node.js

**Goal:** Repeatable, low-risk releases.

### Typical flow

```
Developer pushes to GitHub
        │
        ▼
┌───────────────────┐
│  GitHub Actions   │
│  (ubuntu runner)  │
│  ───────────────  │
│  1. Checkout      │
│  2. Setup PHP/Node│
│  3. composer/npm  │
│  4. npm run build │
│  5. php artisan   │
│     test          │
└─────────┬─────────┘
          │ pass + push to master (not PR)
          ▼
┌───────────────────┐
│  Deploy job       │
│  SSH → server     │
│  git pull         │
│  migrate, cache   │
│  SCP public/build │
└───────────────────┘
```

---

## 2. GitHub Actions fundamentals

### File location

Workflows live in `.github/workflows/*.yml`.

VibeMetrics uses: `.github/workflows/ci.yml`

### Anatomy of a workflow

```yaml
name: CI                    # Display name in Actions tab

on:                         # When to run
  push:
    branches: [master]
  pull_request:
    branches: [master]
  workflow_dispatch:        # Manual "Run workflow" button

jobs:                       # Units of work
  test:
    runs-on: ubuntu-latest  # VM image
    steps:
      - uses: actions/checkout@v4
      - run: composer install

  deploy:
    needs: test             # Runs only after `test` succeeds
    if: github.event_name != 'pull_request'
    runs-on: ubuntu-latest
    steps:
      - run: echo "deploy"
```

### Key concepts

| Concept | Meaning |
|---------|---------|
| **Workflow** | One YAML file |
| **Job** | Group of steps on one runner; jobs can depend on each other |
| **Step** | Single shell command or reusable action |
| **Action** | Reusable unit (`actions/checkout@v4`, `appleboy/ssh-action`) |
| **Runner** | GitHub-hosted VM or self-hosted machine |
| **Artifact** | Files passed between jobs (e.g. `public/build`) |
| **Secret** | Encrypted value — never logged in output |
| **Environment** | Named deploy target with optional approval gates |

### Common triggers

```yaml
on:
  push:
    branches: [master]
  pull_request:
    branches: [master]
  workflow_dispatch:           # Manual run
  schedule:
    - cron: '0 2 * * 1'       # Weekly Monday 02:00 UTC
  release:
    types: [published]         # On GitHub Release
```

### Conditional deploy (critical)

```yaml
deploy:
  needs: test
  if: github.event_name != 'pull_request' && github.ref == 'refs/heads/master'
```

Without `if`, misconfigured workflows could deploy from pull requests.

### Deploy concurrency (recommended)

Prevents two deploys racing on the same server:

```yaml
deploy:
  concurrency:
    group: production-deploy
    cancel-in-progress: false
```

---

## 3. Laravel-specific CI/CD concerns

Laravel production deploys typically require:

| Step | Why |
|------|-----|
| `composer install --no-dev` | Production dependencies only |
| `npm run build` | Vite compiles Vue to `public/build/` |
| `php artisan migrate --force` | Schema updates (non-interactive) |
| `php artisan config:cache` | Faster config reads |
| `php artisan route:cache` | Faster routing |
| `php artisan view:cache` | Precompile Blade |
| `php artisan storage:link` | Once per server, not every deploy |
| `php artisan queue:restart` | Workers reload code after deploy |
| `php artisan down` / `up` | Brief maintenance window |

### Queue and scheduler (on the server, not in CI)

```cron
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

VPS: use **Supervisor** for `queue:work` 24/7.  
Shared hosting: cron every minute with `--stop-when-empty --max-time=55`.

### Testing in CI

VibeMetrics `phpunit.xml` uses SQLite in-memory — ideal for CI:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
<env name="QUEUE_CONNECTION" value="sync"/>
```

CI preparation:

```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # if using file-based sqlite
php artisan migrate --force
php artisan test
```

### Rules

- `.env` is **never** committed — CI creates a throwaway copy
- `public/build/` is often **gitignored** — must be built in CI or on server
- After `config:cache`, use `config('key')` in app code, not `env()` outside config files

---

## 4. Industry standards by hosting type

### Comparison matrix

| Aspect | Shared hosting | VPS | Cloud (AWS/DO/Hetzner) |
|--------|----------------|-----|------------------------|
| SSH | Limited port | Full access | Full control |
| Node.js on server | Usually **no** | Yes | Yes |
| Supervisor | No | Yes | Yes |
| Build assets | CI → SCP/SFTP | Server or CI | CI or server |
| Deploy method | SSH + git pull + SCP | SSH / Forge / Deployer | Forge, Envoyer, K8s, Vapor |
| Zero-downtime | Difficult | Possible | Standard at scale |
| Typical CI | Test + build + SCP | Test + SSH deploy | Test + image or SSH |

### What teams actually use for Laravel

**Solo / shared hosting (VibeMetrics default):**

1. GitHub Actions: test → build → SSH deploy
2. Build frontend in CI (no npm on Hostinger)
3. Upload `public/build` via SCP
4. Queue via cron every minute

**Growing VPS / agency:**

1. GitHub Actions tests on every PR
2. Deploy on merge via SSH or **Laravel Forge**
3. Supervisor for queue
4. Certbot for SSL
5. Optional staging subdomain

**SaaS / team:**

1. PR checks: tests, Pint, PHPStan, frontend lint
2. Staging auto-deploy from `develop`
3. Production deploy with manual approval (GitHub Environments)
4. DB backup before migrate
5. Monitoring (Sentry, `/up`, UptimeRobot)

**Enterprise / high scale:**

- Docker → registry → Kubernetes / ECS
- Laravel Vapor (serverless AWS)
- Canary deploys, feature flags

For VibeMetrics scale, **GitHub Actions + SSH** is correct and widely used.

---

## 5. Pipeline A: With tests (recommended)

This is what `.github/workflows/ci.yml` implements.

### Full example

```yaml
name: CI

on:
  push:
    branches: [master]
  pull_request:
    branches: [master]
  workflow_dispatch:

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, pdo_sqlite, bcmath, intl
          coverage: none

      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: npm

      - name: Install Composer dependencies
        run: composer install --prefer-dist --no-interaction

      - name: Install npm dependencies
        run: npm ci

      - name: Build frontend
        run: npm run build

      - name: Prepare Laravel
        run: |
          cp .env.example .env
          php artisan key:generate
          touch database/database.sqlite

      - name: Run migrations
        run: php artisan migrate --force

      - name: Run tests
        run: php artisan test

      - name: Upload build artifact
        uses: actions/upload-artifact@v4
        with:
          name: public-build
          path: public/build
          retention-days: 7

  deploy:
    name: Deploy Production
    needs: test
    if: github.event_name != 'pull_request'
    runs-on: ubuntu-latest
  #  environment: production   # optional: require manual approval

    steps:
      - uses: actions/checkout@v4

      - name: Download frontend build
        uses: actions/download-artifact@v4
        with:
          name: public-build
          path: public/build

      - name: Deploy application
        uses: appleboy/ssh-action@v1.2.0
        with:
          host: ${{ secrets.SSH_HOST }}
          username: ${{ secrets.SSH_USER }}
          key: ${{ secrets.SSH_KEY }}
          port: ${{ secrets.SSH_PORT }}
          script: |
            set -e
            cd ${{ secrets.DEPLOY_PATH }}
            php artisan down || true
            git pull origin master
            composer install --no-dev --optimize-autoloader --no-interaction
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            php artisan queue:restart || true
            php artisan up

      - name: Archive frontend build
        run: tar -czf public-build.tar.gz -C public build

      - name: Upload build archive
        uses: appleboy/scp-action@v0.1.7
        with:
          host: ${{ secrets.SSH_HOST }}
          username: ${{ secrets.SSH_USER }}
          key: ${{ secrets.SSH_KEY }}
          port: ${{ secrets.SSH_PORT }}
          source: public-build.tar.gz
          target: ${{ secrets.DEPLOY_PATH }}

      - name: Extract build on server
        uses: appleboy/ssh-action@v1.2.0
        with:
          host: ${{ secrets.SSH_HOST }}
          username: ${{ secrets.SSH_USER }}
          key: ${{ secrets.SSH_KEY }}
          port: ${{ secrets.SSH_PORT }}
          script: |
            cd ${{ secrets.DEPLOY_PATH }}
            tar -xzf public-build.tar.gz -C public
            rm -f public-build.tar.gz
```

### Why separate jobs?

| Benefit | Explanation |
|---------|-------------|
| PR safety | PRs run tests only — never deploy |
| Artifact integrity | Same `public/build` that passed tests goes to production |
| Fail-fast | Broken tests block deploy (`needs: test`) |

### Optional enhancements

```yaml
# Lint job (parallel)
lint:
  runs-on: ubuntu-latest
  steps:
    - uses: actions/checkout@v4
    - uses: shivammathur/setup-php@v2
      with:
        php-version: '8.3'
    - run: composer install
    - run: ./vendor/bin/pint --test

# MySQL service (if tests need real MySQL instead of sqlite)
services:
  mysql:
    image: mysql:8
    env:
      MYSQL_ROOT_PASSWORD: password
      MYSQL_DATABASE: testing
```

---

## 6. Pipeline B: Without tests

**When acceptable:** Prototypes, personal projects with no test suite yet.

**Risk:** Broken code can ship to production.

### Minimal deploy-only workflow

```yaml
name: Deploy

on:
  push:
    branches: [master]
  workflow_dispatch:

jobs:
  build-and-deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring

      - uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: npm

      - run: composer install --no-dev --optimize-autoloader --no-interaction
      - run: npm ci && npm run build

      # Smoke check instead of full test suite
      - run: |
          cp .env.example .env
          php artisan key:generate
          php artisan config:clear
          php artisan route:list > /dev/null

      - name: Deploy via SSH
        uses: appleboy/ssh-action@v1.2.0
        with:
          host: ${{ secrets.SSH_HOST }}
          username: ${{ secrets.SSH_USER }}
          key: ${{ secrets.SSH_KEY }}
          port: ${{ secrets.SSH_PORT }}
          script: |
            set -e
            cd ${{ secrets.DEPLOY_PATH }}
            php artisan down || true
            git pull origin master
            composer install --no-dev --optimize-autoloader --no-interaction
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            php artisan queue:restart || true
            php artisan up

      # ... SCP public/build as in Pipeline A
```

### Middle ground (recommended transition)

```yaml
- name: Critical smoke tests
  run: |
    cp .env.example .env
    php artisan key:generate
    touch database/database.sqlite
    php artisan migrate --force
    php artisan test --filter=HealthTest
```

Add more tests over time until you reach Pipeline A.

---

## 7. Shared hosting pattern

**Examples:** Hostinger Business, many cPanel hosts

### Constraints

| Limitation | Implication |
|------------|-------------|
| No Node.js | Build in CI, SCP `public/build/` |
| No Supervisor | Queue via cron every minute |
| Non-standard SSH port | Set `SSH_PORT` secret (e.g. `65002`) |
| `public/build` gitignored | Upload after every deploy |

### Step-by-step setup

#### 1. Server one-time setup

```bash
ssh -p 65002 user@host.hostinger.com
cd ~/domains/yourdomain.com
git clone https://github.com/you/vibemetrics.git
cd vibemetrics
cp .env.example .env
nano .env
php artisan key:generate
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
chmod -R ug+rwx storage bootstrap/cache
```

Set document root to `.../vibemetrics/public` in hPanel.

#### 2. Cron jobs (hPanel)

```cron
* * * * * /usr/bin/php /home/USER/domains/yourdomain.com/vibemetrics/artisan schedule:run >> /dev/null 2>&1
* * * * * /usr/bin/php /home/USER/domains/yourdomain.com/vibemetrics/artisan queue:work database --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

#### 3. Deploy SSH key

```bash
ssh-keygen -t ed25519 -f deploy_key -N "" -C "github-deploy"
```

- Add `deploy_key.pub` to Hostinger SSH keys
- Add private key to GitHub secret `SSH_KEY`

#### 4. GitHub secrets

| Secret | Example |
|--------|---------|
| `SSH_HOST` | `xxx.hostinger.com` |
| `SSH_USER` | `u123456789` |
| `SSH_PORT` | `65002` |
| `SSH_KEY` | Full private key PEM |
| `DEPLOY_PATH` | `/home/u.../domains/yourdomain.com/vibemetrics` |

#### 5. Push to `master` → Actions runs

See also: [Hostinger deployment guide](../deployment/HOSTINGER-SHARED-CICD.md)

### Shared hosting edge cases

| Edge case | Problem | Fix |
|-----------|---------|-----|
| Forgot SCP `public/build` | Broken CSS/JS | Always upload artifact after deploy |
| `git pull` conflicts | Deploy fails | Never edit files on server manually |
| Composer OOM | Install fails | `COMPOSER_MEMORY_LIMIT=-1 composer install` |
| Branch mismatch | Pull fails | Use same branch everywhere (`master`) |
| `artisan down` stuck | Site offline | SSH in, run `php artisan up` |
| Migration fails | Partial deploy | Fix migration, backup DB first |
| Two deploy workflows | Double deploy | Keep one workflow file active |

---

## 8. VPS pattern

**Examples:** DigitalOcean, Hetzner, Linode, AWS EC2

### Advantages

- Node.js on server → `npm run build` during deploy
- Supervisor → 24/7 queue worker
- Nginx + Certbot → standard Laravel stack

### Deploy script on server

`docs/scripts/deploy-vps.sh`:

```bash
cd /var/www/vibemetrics
php artisan down || true
git pull origin master
composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart
php artisan up
```

### VPS GitHub Actions options

**Option A — Build on server (simpler workflow):**

```yaml
- uses: appleboy/ssh-action@v1.2.0
  with:
    script: |
      cd /var/www/vibemetrics
      APP_DIR=/var/www/vibemetrics bash docs/scripts/deploy-vps.sh
```

No SCP needed.

**Option B — Build in CI (consistent artifacts):**

Same as shared hosting: artifact + SCP. Server skips `npm run build`.

### Supervisor (one-time)

```ini
[program:vibemetrics-worker]
command=php /var/www/vibemetrics/artisan queue:work database --sleep=3 --tries=3
autostart=true
autorestart=true
user=deploy
stdout_logfile=/var/www/vibemetrics/storage/logs/worker.log
```

See also: [VPS deployment guide](../deployment/VPS-CLOUD-CICD.md)

### VPS edge cases

| Edge case | Fix |
|-----------|-----|
| Permission denied on `storage/` | `chown -R deploy:www-data storage bootstrap/cache` |
| OPcache serves old PHP | `sudo systemctl reload php8.3-fpm` |
| Nginx 502 | Check php-fpm socket in nginx config |
| Deploy as root | Use dedicated `deploy` user |
| Brief downtime | `artisan down`/`up` causes outage; use Forge/Envoyer for zero-downtime |

---

## 9. Cloud and managed patterns

### Laravel Forge (common for Laravel VPS)

- GitHub integration → auto-deploy on push
- Manages Nginx, SSL, Supervisor, scheduler
- Minimal YAML required
- **Industry standard** for Laravel on VPS

### Laravel Envoyer

- Zero-downtime deploys (symlinked releases)
- Deploy hooks for migrate and queue restart
- Pairs with Forge or any VPS

### Laravel Vapor

- Serverless on AWS Lambda
- Deploy via Vapor CLI, not traditional SSH
- For high-scale SaaS

### Platform-as-a-Service

| Platform | Notes |
|----------|-------|
| DigitalOcean App Platform | Buildpack or Dockerfile |
| Railway / Render | Set build/release commands in dashboard |
| AWS Elastic Beanstalk | More configuration overhead |

Typical PaaS commands:

```bash
# Build
composer install --no-dev && npm ci && npm run build

# Release
php artisan migrate --force
```

### GitHub Actions vs Forge

| Use GitHub Actions | Use Forge + Envoyer |
|--------------------|---------------------|
| Shared hosting (Hostinger) | Own VPS, want GUI |
| Full YAML control | Zero-downtime deploys |
| Free for public repos | Less DevOps time |

Many teams use **both**: Forge deploys, GitHub Actions tests on PR.

---

## 10. Secrets, environments, and branches

### GitHub Secrets

Repository → Settings → Secrets and variables → Actions

Never commit secrets in YAML.

### GitHub Environments (production gate)

```yaml
deploy:
  environment: production
```

Settings → Environments → production:

- Required reviewers before deploy
- Environment-specific secrets (staging vs production)

### Branch strategy

| Branch | CI | Deploy |
|--------|-----|--------|
| `feature/*` | Optional | Never |
| PR → `master` | Full tests | Never |
| `master` | Full tests | Production |
| `develop` | Full tests | Staging (optional) |

### Protect `master`

Settings → Branches → Branch protection rules:

- Require pull request before merge
- Require status check: `test` job
- Optionally block direct pushes

> VibeMetrics uses `master` as the default branch. Keep workflow triggers, `git pull`, and docs aligned on the same branch name.

---

## 11. Edge cases and failure modes

### Deploy fails after migrate but before `up`

Site may be in maintenance mode.

**Fix:** SSH in, resolve issue, `php artisan up`.

### Destructive migrations

**Industry practice:**

- Backup database before deploy
- Test migrations on staging first
- Prefer forward-fix migrations over `rollback` in production

### Rolling back code

```bash
git checkout <previous-commit>
composer install --no-dev
php artisan config:cache
php artisan queue:restart
```

Rolling back code without rolling back migrations can break the app.

### Queue workers run old code

Always run `php artisan queue:restart` after deploy.

### Concurrent deploys

Two pushes → two deploys racing.

**Fix:**

```yaml
concurrency:
  group: production-deploy
  cancel-in-progress: false
```

### Fork PRs and secrets

Fork pull requests do not receive repository secrets. Deploy must stay behind `if: github.event_name != 'pull_request'`.

### `config:cache` and `env()`

After caching config, `env()` outside `/config` files returns `null` in production. Use `config('app.url')` instead.

### Missing `public/build`

Most common Laravel deploy bug on shared hosting. Symptoms: white page or unstyled UI.

### PHP version mismatch

Align CI, local, and server: e.g. all on PHP 8.3.

### Composer / npm cache in CI

```yaml
- uses: actions/setup-node@v4
  with:
    cache: npm
```

---

## 12. VibeMetrics workflow explained

File: `.github/workflows/ci.yml`

| Job | Purpose |
|-----|---------|
| `test` | Install deps, build frontend, run migrations + tests, upload `public/build` artifact |
| `deploy` | SSH git pull + artisan optimize; SCP and extract build tarball |

**Design choices (correct for Hostinger):**

- Tests gate deploy
- Frontend built in CI (no npm on shared host)
- Maintenance mode during deploy
- Artifact ensures tested assets match production

**Recommended improvements:**

1. Add `concurrency` group on deploy job
2. Add `environment: production` for manual approval
3. Add deploy failure notification (Slack/email action)
4. Run `php artisan storage:link` once on server (not each deploy)
5. Keep branch name `master` consistent everywhere

---

## 13. Checklists

### First-time CI/CD setup

- [ ] Tests pass locally: `composer test`
- [ ] `npm run build` produces `public/build`
- [ ] Server has `.env`, `APP_KEY`, database configured
- [ ] Document root points to `public/`
- [ ] SSH deploy key on server + all GitHub secrets set
- [ ] Cron: scheduler + queue (shared) or Supervisor (VPS)
- [ ] Workflow deploys only on push to `master`, not PRs
- [ ] Verify `https://yourdomain.com/up` after first deploy

### Every release

- [ ] PR merged after CI green
- [ ] Migrations reviewed
- [ ] `public/build` deployed (shared hosting)
- [ ] Admin → Health checks pass
- [ ] Dashboard and tracking snippet spot-checked

### Security

- [ ] `.env` in `.gitignore`
- [ ] `SSH_KEY` only in GitHub Secrets
- [ ] `APP_DEBUG=false` in production
- [ ] Never run `php artisan db:seed` in production

---

## Quick reference: pattern by host

```
Shared hosting (Hostinger)
  CI:  test + npm build + artifact
  CD:  SSH git pull + SCP public/build
  Queue: cron every minute

VPS (DigitalOcean, Hetzner)
  CI:  test (+ optional lint)
  CD:  SSH script (build on server OR SCP from CI)
  Queue: Supervisor
  Tools: Forge / Envoyer optional

Cloud PaaS (Railway, Render)
  CI:  often built into platform
  CD:  git push triggers platform build

Enterprise
  Docker → registry → K8s / Vapor
```

---

## Related guides

- [Deployment & Operations](./08-deployment-operations.md) — production checklist
- [Hostinger CI/CD](../deployment/HOSTINGER-SHARED-CICD.md)
- [VPS CI/CD](../deployment/VPS-CLOUD-CICD.md)
- [Configuration](./06-configuration.md) — `.env` reference
