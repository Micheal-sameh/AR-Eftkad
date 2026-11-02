# syntax=docker/dockerfile:1

# ---- Frontend assets ----
FROM node:22.23.4-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN --mount=type=cache,target=/root/.npm npm ci
COPY resources ./resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

# ---- PHP dependencies ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN --mount=type=secret,id=github_token \
    --mount=type=cache,target=/tmp/cache \
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
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# ---- Runtime image: php-fpm + nginx in one container, via supervisord ----
# php + pdo_mysql + nginx + supervisor come from the prebuilt base image (Dockerfile.base,
# built once by the "base" service in docker-compose.yml), so this build only does the
# npm / composer stages above and the file copies below.
FROM eftkad-php-base AS app

WORKDIR /var/www/html

COPY --from=vendor --chown=www-data:www-data /app ./
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build

RUN mkdir -p /run/nginx /var/cache/nginx/pages \
    && chown -R nginx:nginx /var/cache/nginx

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/php/fpm-pool.conf /usr/local/etc/php-fpm.d/zz-pool.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf", "-n"]
