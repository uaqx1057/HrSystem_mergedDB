#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${1:-/var/www/hr/current}"
APP_URL="${2:-https://hr.yourdomain.com}"

cd "${APP_DIR}"

echo "[1/6] Laravel config check"
php artisan about >/dev/null

echo "[2/6] Env drivers"
php -r 'echo "CACHE_DRIVER=".getenv("CACHE_DRIVER").PHP_EOL; echo "SESSION_DRIVER=".getenv("SESSION_DRIVER").PHP_EOL; echo "QUEUE_CONNECTION=".getenv("QUEUE_CONNECTION").PHP_EOL;'

echo "[3/6] Redis ping"
redis-cli -h "${REDIS_HOST:-127.0.0.1}" -p "${REDIS_PORT:-6379}" ping

echo "[4/6] Queue workers status"
sudo supervisorctl status hr-worker:* || true
sudo supervisorctl status hr-scheduler || true

echo "[5/6] Public endpoint"
curl -I "${APP_URL}" | head -n 1

echo "[6/6] Done"
echo "HR VPS preflight check completed"
