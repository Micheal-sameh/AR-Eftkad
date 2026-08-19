# syntax=docker/dockerfile:1

# ---- Frontend assets ----
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

# ---- PHP dependencies ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN --mount=type=secret,id=github_token \
    if [ -s /run/secrets/github_token ]; then \
        composer config --global github-oauth.github.com "$(cat /run/secrets/github_token)"; \
    fi \
    && composer update avarewase/sso-client \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --ignore-platform-reqs \
    && composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --no-autoloader \
        --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev

# ---- Runtime image: php-fpm + nginx in one container, via supervisord ----
FROM php:8.2-fpm-alpine AS app

RUN apk add --no-cache \
        nginx \
        supervisor \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        icu-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl

WORKDIR /var/www/html

COPY --from=vendor /app ./
COPY --from=frontend /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/html \
    && mkdir -p /run/nginx

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf", "-n"]
