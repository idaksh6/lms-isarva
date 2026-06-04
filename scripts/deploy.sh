#!/usr/bin/env bash
# Run on the server after git pull (manual or from GitHub Actions).
set -euo pipefail

APP_DIR="${APP_DIR:-/home/lms.isarvait.com}"
WEB_ROOT="${WEB_ROOT:-${APP_DIR}/public_html}"

cd "$APP_DIR"

echo "==> Deploying LMS in ${APP_DIR}"

if [[ ! -f .env ]]; then
    echo "ERROR: .env missing. Create it on the server first (never commit .env)." >&2
    exit 1
fi

git pull origin "${DEPLOY_BRANCH:-main}"

composer install --no-dev --optimize-autoloader --no-interaction

if command -v npm >/dev/null 2>&1; then
    npm ci --ignore-scripts
    npm run build
else
    echo "WARN: npm not found; assuming public/build was built in CI."
fi

# Laravel reads manifest from public/build; browser loads from public_html
mkdir -p "${APP_DIR}/public/build" "${WEB_ROOT}/build"
rsync -a --delete "${APP_DIR}/public/build/" "${WEB_ROOT}/build/"
rsync -a "${APP_DIR}/public/build/" "${APP_DIR}/public/build/"

# Web-visible static files (if not already in public_html)
for item in index.php .htaccess robots.txt favicon.ico images; do
    if [[ -e "${APP_DIR}/public/${item}" ]]; then
        rsync -a "${APP_DIR}/public/${item}" "${WEB_ROOT}/"
    fi
done

php artisan storage:link 2>/dev/null || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

echo "==> Deploy finished. Check https://lms.isarvait.com/up"
