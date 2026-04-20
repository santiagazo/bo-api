# =============================================================================
# Built Ox API - Production Dockerfile
# =============================================================================

ARG PHP_VERSION=8.4
ARG COMPOSER_VERSION=2.8

# -----------------------------------------------------------------------------
# Stage 1: Composer dependencies
# -----------------------------------------------------------------------------
FROM composer:${COMPOSER_VERSION} AS composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# -----------------------------------------------------------------------------
# Stage 2: Production image
# -----------------------------------------------------------------------------
FROM php:${PHP_VERSION}-fpm-alpine

ARG WWWUSER=1000
ARG WWWGROUP=1000
ARG TZ=UTC

ENV TZ=${TZ} \
    USER=laravel \
    ROOT=/var/www/html

WORKDIR ${ROOT}

# Install system dependencies
RUN apk update && apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpq-dev \
    libzip-dev \
    oniguruma-dev \
    && rm -rf /var/cache/apk/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    zip \
    opcache \
    bcmath

# Create user
RUN addgroup -g ${WWWGROUP} ${USER} \
    && adduser -D -h ${ROOT} -G ${USER} -u ${WWWUSER} -s /bin/sh ${USER}

# Create necessary directories
RUN mkdir -p /var/log/supervisor /var/run/supervisor /run/nginx /tmp/nginx \
    && chown -R ${USER}:${USER} /var/log /var/run /run/nginx /tmp/nginx

# Copy nginx config
COPY --chown=${WWWUSER}:${WWWUSER} docker/nginx.conf /etc/nginx/nginx.conf

# Copy PHP config
COPY --chown=${WWWUSER}:${WWWUSER} docker/php.ini /usr/local/etc/php/php.ini
COPY --chown=${WWWUSER}:${WWWUSER} docker/fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf

# Copy supervisor config
COPY --chown=${WWWUSER}:${WWWUSER} docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy entrypoint
COPY --chown=${WWWUSER}:${WWWUSER} docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copy application code
COPY --chown=${WWWUSER}:${WWWUSER} . .

# Copy composer dependencies
COPY --from=composer /app/vendor ./vendor

# Set permissions for storage and cache
RUN chown -R ${USER}:${USER} ${ROOT} \
    && chmod -R 775 ${ROOT}/storage ${ROOT}/bootstrap/cache \
    && rm -f ${ROOT}/bootstrap/cache/*.php

USER ${USER}

EXPOSE 8080

ENTRYPOINT ["entrypoint.sh"]

