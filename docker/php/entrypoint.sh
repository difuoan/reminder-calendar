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

echo ">>> Running database migrations..."
php /var/www/html/scripts/migrate.php || echo "!!! Migration failed – check DB_HOST / DB_* environment variables"

echo ">>> Starting reminder cron job..."
echo '* * * * * php /var/www/html/scripts/send_reminders.php >> /tmp/reminders.log 2>&1' | crontab -
crond -b

echo ">>> Starting PHP-FPM..."
php-fpm -D

echo ">>> Starting Nginx..."
exec nginx -g "daemon off;"
