# ============================================================
# PHP 8.2-FPM + Nginx application image
#
# Installs the PHP extensions required by the application
# (PDO MySQL for the database, mbstring for string handling)
# and sets up Nginx to serve the public/ directory.
# ============================================================

FROM php:8.2-fpm-alpine

# Install system packages and PHP extensions
# oniguruma-dev is required by the mbstring PHP extension
RUN apk add --no-cache nginx curl oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring

# Copy Nginx virtual-host configuration
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# PHP-FPM pool config: keep environment variables (clear_env=no)
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zzz-www-override.conf

# Install Composer (PHP dependency manager)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory to project root
WORKDIR /var/www/html

# Copy entrypoint that installs Composer deps and starts services
COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
