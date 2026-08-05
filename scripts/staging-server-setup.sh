#!/usr/bin/env bash
# One-time LMS setup on STAGING CyberPanel site (lmsdev.isarva.in).
# Run as the CyberPanel site SSH user — NOT as root, NOT on production.
#
#   ssh <lmsdev-site-user>@139.84.140.77
#   bash staging-server-setup.sh
set -euo pipefail

DOMAIN="${DOMAIN:-lmsdev.isarva.in}"
APP_DIR="${APP_DIR:-/home/${DOMAIN}}"
REPO="${REPO:-git@github.com:idaksh6/lms-isarva.git}"
BRANCH="${BRANCH:-main}"

if [[ "$(whoami)" == "root" ]]; then
  echo "ERROR: Run as the CyberPanel site user for ${DOMAIN}, not root." >&2
  exit 1
fi

if [[ "$APP_DIR" == *"lms.isarvait.com"* ]] || [[ "$DOMAIN" == *"lms.isarvait.com"* ]]; then
  echo "ERROR: This script is for staging (lmsdev.isarva.in) only." >&2
  exit 1
fi

echo "=== LMS staging setup: ${DOMAIN} ==="
echo "    User: $(whoami)"
echo "    App dir: ${APP_DIR}"

mkdir -p "$APP_DIR"
cd "$APP_DIR"

if [[ ! -d .git ]]; then
  git clone "$REPO" .
  git checkout "$BRANCH"
else
  git fetch origin "$BRANCH"
  git checkout "$BRANCH"
  git reset --hard "origin/$BRANCH"
fi

if [[ ! -f .env ]]; then
  if [[ -f .env.staging.example ]]; then
    cp .env.staging.example .env
  else
    cp .env.example .env
    sed -i.bak 's|APP_URL=.*|APP_URL=https://lmsdev.isarva.in|' .env
    sed -i.bak 's|APP_ENV=.*|APP_ENV=staging|' .env
    sed -i.bak 's|LMS_SHOW_DEMO_CREDENTIALS=.*|LMS_SHOW_DEMO_CREDENTIALS=true|' .env
    rm -f .env.bak
  fi
  echo ""
  echo ">>> Edit ${APP_DIR}/.env — set DB_DATABASE, DB_USERNAME, DB_PASSWORD, then re-run this script."
  exit 0
fi

if grep -q 'CHANGE_ME' .env 2>/dev/null || ! grep -q '^APP_KEY=base64:' .env; then
  PHP=""
  for c in /usr/local/lsws/lsphp83/bin/php /usr/bin/php8.3 php; do
    [[ -x "$c" ]] && PHP="$c" && break
  done
  [[ -n "$PHP" ]] && "$PHP" artisan key:generate --force || true
fi

if grep -q 'CHANGE_ME' .env 2>/dev/null; then
  echo "ERROR: Update DB credentials in .env (remove CHANGE_ME) before continuing." >&2
  exit 1
fi

bash scripts/staging-deploy.sh

echo ""
echo "=== DONE ==="
echo "Open: https://${DOMAIN}/login"
echo "Demo accounts appear on the login page (staging only)."
