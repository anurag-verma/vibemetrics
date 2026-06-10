#!/bin/bash
# Hostinger shared hosting — manual deploy script
# Set APP_DIR to your project path on the server before running.

set -euo pipefail

APP_DIR="${APP_DIR:-/home/USER/domains/yourdomain.com/vibemetrics}"

if [[ ! -f "$APP_DIR/artisan" ]]; then
    echo "Error: artisan not found in APP_DIR=$APP_DIR"
    echo "Set APP_DIR to your VibeMetrics root, e.g.:"
    echo "  APP_DIR=/home/u123456789/domains/example.com/vibemetrics ./scripts/deploy-hostinger.sh"
    exit 1
fi

cd "$APP_DIR"

echo "==> Maintenance mode"
php artisan down || true

echo "==> Pull latest code"
git pull origin main

echo "==> Composer"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Migrations"
php artisan migrate --force

echo "==> Cache"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Queue restart signal"
php artisan queue:restart || true

echo "==> Back online"
php artisan up

echo ""
echo "Deploy complete. Remember to upload public/build/ from CI or local npm run build."
echo "Hostinger shared hosting does not include npm on the server."
