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
FROM php:8.2.35-fpm-alpine AS app

# Only what the app actually needs. Nothing in composer.lock requires gd, zip, intl,
# exif, pcntl or bcmath. (mbstring, ctype, tokenizer, openssl, etc. are already built
# into the base image, and so is opcache.so - it only has to be enabled.)
#
# pdo_mysql is the one extension that is not in the base image. Compiling it means
# downloading a ~150 MB compiler toolchain, which took 10-25 minutes on our server, so
# the module is committed prebuilt (docker/php/ext/README.md says how it was made and how
# to regenerate it). The check below fails the build if it ever stops matching the base image.
#
# Alpine's default CDN (dl-cdn.alpinelinux.org) only gives ~65 KB/s to our server, so nginx
# and supervisor come from a nearer mirror; override with --build-arg APK_MIRROR=dl-cdn.alpinelinux.org.
# apk still verifies package signatures, so the mirror is not trusted blindly.
ARG APK_MIRROR=mirrors.aliyun.com
COPY docker/php/ext/pdo_mysql.so /tmp/pdo_mysql.so
RUN sed -i "s#dl-cdn.alpinelinux.org#${APK_MIRROR}#g" /etc/apk/repositories \
    && apk add --no-cache nginx supervisor \
    && mv /tmp/pdo_mysql.so "$(php-config --extension-dir)/pdo_mysql.so" \
    && docker-php-ext-enable pdo_mysql opcache \
    && php -m | grep -qx pdo_mysql \
    && php -m | grep -qx 'Zend OPcache'

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
