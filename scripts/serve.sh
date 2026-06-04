#!/usr/bin/env bash
# Laravel's "php artisan serve" starts a child "php -S" without upload limits.
# This script runs the built-in server with php-local.ini so large submissions work.

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

HOST="${1:-127.0.0.1}"
PORT="${2:-8000}"
INI="${ROOT}/php-local.ini"
ROUTER="${ROOT}/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"

if [[ ! -f "$ROUTER" ]]; then
    echo "Laravel server router not found. Run: composer install" >&2
    exit 1
fi

echo "Starting LMS at http://${HOST}:${PORT}"
echo "Upload limit: $(php -c "$INI" -r "echo ini_get('upload_max_filesize');") (post_max_size: $(php -c "$INI" -r "echo ini_get('post_max_size');"))"
echo "Press Ctrl+C to stop."
echo ""

exec php -c "$INI" -S "${HOST}:${PORT}" "$ROUTER"
