# syntax=docker/dockerfile:1

# ---- dev: PHP CLI + Composer, project is bind-mounted, runs the framework dev server
FROM php:8.4-cli AS dev

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql

WORKDIR /var/www/html

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# ---- assets: Vite build runs in Node so the production image ships prebuilt assets
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# ---- production: nginx in front of php-fpm, both running as the non-root www-data user
FROM php:8.4-fpm AS production

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends nginx unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql \
    && rm -f /etc/nginx/sites-enabled/default

COPY docker/nginx.conf /etc/nginx/nginx.conf.template
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

# The host injects $PORT; the entrypoint writes it into the nginx config.
ENV PORT=8080

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-autoloader --no-scripts --prefer-dist

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x /usr/local/bin/entrypoint

USER www-data

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint"]
