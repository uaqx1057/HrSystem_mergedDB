#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$1"
PHP_BIN="${PHP_BIN:-php}"

if [[ -z "${APP_DIR}" ]]; then
  echo "Usage: deploy_laravel.sh /var/www/<app>/current"
  exit 1
fi

cd "${APP_DIR}"

${PHP_BIN} artisan down || true
${PHP_BIN} artisan migrate --force
${PHP_BIN} artisan config:cache
${PHP_BIN} artisan route:cache
${PHP_BIN} artisan view:cache
${PHP_BIN} artisan storage:link || true
${PHP_BIN} artisan up

echo "Laravel deploy steps completed for ${APP_DIR}"
