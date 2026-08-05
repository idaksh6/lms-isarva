#!/usr/bin/env bash
# Deploy LMS on STAGING only (lmsdev.isarva.in). Does not touch production.
set -euo pipefail

APP_DIR="${APP_DIR:-/home/lmsdev.isarva.in}"
WEB_ROOT="${WEB_ROOT:-${APP_DIR}/public_html}"

cd "$APP_DIR"

echo "==> Staging deploy in ${APP_DIR} (NOT production)"

if [[ "$APP_DIR" == *"lms.isarvait.com"* ]]; then
  echo "ERROR: Refusing to run staging deploy on production path." >&2
  exit 1
fi

if [[ ! -f .env ]]; then
  echo "ERROR: .env missing. Copy .env.staging.example and configure DB first." >&2
  exit 1
fi

git fetch origin "${DEPLOY_BRANCH:-main}"
git reset --hard "origin/${DEPLOY_BRANCH:-main}"

PHP=""
for c in /usr/local/lsws/lsphp83/bin/php /usr/bin/php8.3 /opt/alt/php83/usr/bin/php php; do
  if [[ -x "$c" ]] && "$c" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null | grep -qE '^8\.(3|4|5)$'; then
    PHP="$c"
    break
  fi
done
if [[ -z "$PHP" ]]; then
  echo "ERROR: PHP 8.3+ CLI not found." >&2
  exit 1
fi
echo "Using PHP: $PHP"

"$PHP" "$(command -v composer)" install --no-dev --optimize-autoloader --no-interaction

if command -v npm >/dev/null 2>&1; then
  npm ci --ignore-scripts
  npm run build
else
  echo "WARN: npm not found; using public/build from CI if uploaded."
fi

mkdir -p "${APP_DIR}/public/build" "${WEB_ROOT}/build"
if [[ -d "${APP_DIR}/build" ]]; then
  rsync -a --delete "${APP_DIR}/build/" "${APP_DIR}/public/build/"
fi
rsync -a --delete "${APP_DIR}/public/build/" "${WEB_ROOT}/build/"

for item in index.php .htaccess robots.txt favicon.ico images; do
  if [[ -e "${APP_DIR}/public/${item}" ]]; then
    rsync -a "${APP_DIR}/public/${item}" "${WEB_ROOT}/"
  fi
done

"$PHP" artisan storage:link 2>/dev/null || true
"$PHP" artisan migrate --force
"$PHP" artisan db:seed --force 2>/dev/null || true
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache

chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

echo "==> Staging deploy finished. Check https://lmsdev.isarva.in/up"
