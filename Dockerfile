# ============================================================
# Production Dockerfile – used by Railway (and any other
# container host that builds from the repository root).
#
# Differences from docker/php/Dockerfile (local dev):
#   • COPY . . bakes the source code into the image
#     (no bind-mount needed in production)
#   • .dockerignore excludes .env, vendor/, .git
# ============================================================

FROM php:8.2-fpm-alpine

# Install system packages and PHP extensions
RUN apk add --no-cache nginx curl oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring

# Copy Nginx virtual-host configuration
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Bake application source into the image
COPY . .

# Copy and enable entrypoint
COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
