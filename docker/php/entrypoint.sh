#!/bin/sh
# ============================================================
# Container entrypoint
#
# Runs once when the container starts:
#   1. Installs PHP Composer dependencies
#   2. Runs database migrations (idempotent – safe to re-run)
#   3. Starts the reminder cron job (every minute)
#   4. Starts PHP-FPM as a background daemon
#   5. Starts Nginx in the foreground (keeps container alive)
# ============================================================

set -e

echo ">>> Installing Composer dependencies..."
composer install --no-interaction --prefer-dist 2>&1

echo ">>> Writing .env from environment variables..."
{
  printf "DB_HOST=%s\n"            "${DB_HOST:-db}"
  printf "DB_PORT=%s\n"            "${DB_PORT:-3306}"
  printf "DB_NAME=%s\n"            "${DB_NAME:-calendar}"
  printf "DB_USER=%s\n"            "${DB_USER:-calendar}"
  printf "DB_PASS=%s\n"            "${DB_PASS:-calendar}"
  printf "MAIL_HOST=%s\n"          "${MAIL_HOST:-mail}"
  printf "MAIL_PORT=%s\n"          "${MAIL_PORT:-1025}"
  printf "MAIL_ENCRYPTION=%s\n"    "${MAIL_ENCRYPTION:-}"
  printf "MAIL_USER=%s\n"          "${MAIL_USER:-}"
  printf "MAIL_PASS=%s\n"          "${MAIL_PASS:-}"
  printf "MAIL_FROM=%s\n"          "${MAIL_FROM:-noreply@calendar.local}"
  printf "MAIL_FROM_NAME=%s\n"     "${MAIL_FROM_NAME:-Reminder Calendar}"
  printf "APP_URL=%s\n"            "${APP_URL:-http://localhost:8080}"
} > /var/www/html/.env
echo "  DB_HOST resolved to: ${DB_HOST:-db}"

echo ">>> Running database migrations..."
php /var/www/html/scripts/migrate.php || echo "!!! Migration failed – check DB_HOST / DB_* environment variables"

echo ">>> Starting reminder cron job..."
echo '* * * * * php /var/www/html/scripts/send_reminders.php >> /tmp/reminders.log 2>&1' | crontab -
crond -b

echo ">>> Starting PHP-FPM..."
php-fpm -D

echo ">>> Starting Nginx on port ${PORT:-80}..."
sed -i "s/listen 80;/listen ${PORT:-80};/" /etc/nginx/http.d/default.conf
exec nginx -g "daemon off;"
