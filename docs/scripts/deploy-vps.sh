#!/bin/bash
# VPS / cloud server — manual or CI deploy script

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/vibemetrics}"

if [[ ! -f "$APP_DIR/artisan" ]]; then
    echo "Error: artisan not found in APP_DIR=$APP_DIR"
    exit 1
fi

cd "$APP_DIR"

echo "==> Maintenance mode"
php artisan down || true

echo "==> Pull latest code"
git pull origin main

echo "==> Composer"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Frontend build"
npm ci
npm run build

echo "==> Migrations"
php artisan migrate --force

echo "==> Cache"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Queue restart"
php artisan queue:restart

echo "==> Back online"
php artisan up

echo "Deploy finished at $(date)"
