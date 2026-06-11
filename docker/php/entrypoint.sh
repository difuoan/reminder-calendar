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
cat > /var/www/html/.env << EOF
DB_HOST=${DB_HOST:-db}
DB_PORT=${DB_PORT:-3306}
DB_NAME=${DB_NAME:-calendar}
DB_USER=${DB_USER:-calendar}
DB_PASS=${DB_PASS:-calendar}
MAIL_HOST=${MAIL_HOST:-mail}
MAIL_PORT=${MAIL_PORT:-1025}
MAIL_ENCRYPTION=${MAIL_ENCRYPTION:-}
MAIL_USER=${MAIL_USER:-}
MAIL_PASS=${MAIL_PASS:-}
MAIL_FROM=${MAIL_FROM:-noreply@calendar.local}
MAIL_FROM_NAME=${MAIL_FROM_NAME:-Reminder Calendar}
APP_URL=${APP_URL:-http://localhost:8080}
EOF

echo ">>> Running database migrations..."
php /var/www/html/scripts/migrate.php || echo "!!! Migration failed – check DB_HOST / DB_* environment variables"

echo ">>> Starting reminder cron job..."
echo '* * * * * php /var/www/html/scripts/send_reminders.php >> /tmp/reminders.log 2>&1' | crontab -
crond -b

echo ">>> Starting PHP-FPM..."
php-fpm -D

echo ">>> Starting Nginx..."
exec nginx -g "daemon off;"
