ARG BASE_IMAGE=ghcr.io/masgeek/laravel-frankenphp-base:php8.4

# -----------------------------------
# Build stage for frontend assets
# -----------------------------------
FROM node:24-alpine AS frontend-build

WORKDIR /app

# Install pnpm globally
RUN npm install -g pnpm

# Copy only the package.json and pnpm-lock.yaml to leverage Docker cache
COPY package.json pnpm-lock.yaml pnpm-workspace.yaml ./

# Install dependencies
RUN pnpm install --frozen-lockfile

# Copy the rest of the frontend source
COPY . .

# Build the application
RUN pnpm build

# -----------------------------------
# Final production stage
# -----------------------------------
FROM ${BASE_IMAGE}

ENV AGENT_MQTT_ENABLED=false
ARG APP_BASE_PATH=/var/www/html/fuelrod

ENV APP_BASE_PATH=${APP_BASE_PATH} \
    APP_PUBLIC_PATH=${APP_BASE_PATH}/public

USER root

# Set working directory
WORKDIR ${APP_BASE_PATH}

# Install PHP dependencies in a cacheable layer.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts

# Copy application files
COPY . .

# Finish framework discovery and ensure the FrankenPHP worker is available.
RUN composer dump-autoload --no-dev --optimize \
    && php artisan octane:install --server=frankenphp --no-interaction


# Copy frontend build
COPY --from=frontend-build --chown=www-data:www-data /app/public/build ./public/build

# Set correct ownership and permissions
RUN chown -R www-data:www-data \
    "${APP_BASE_PATH}" \
    && chmod -R u+rwX,go-rwx "${APP_BASE_PATH}/storage" "${APP_BASE_PATH}/bootstrap/cache"

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl --fail --silent http://127.0.0.1/up > /dev/null || exit 1

# Switch to www-data user for runtime
USER www-data

# Start all services using Supervisor
CMD ["/usr/local/bin/start.sh"]
