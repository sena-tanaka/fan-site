# ステージ1: Composer
FROM composer:latest AS composer-builder
WORKDIR /app
COPY src/composer.json src/composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# ステージ2: Node.js（npm run build で public/build を作る）
FROM node:20 AS node-builder
WORKDIR /app
COPY src/package*.json ./
RUN npm ci
COPY src/resources ./resources
COPY src/vite.config.js src/tailwind.config.js src/postcss.config.js ./
RUN npm run build

# ステージ3: 最終イメージ（8.2 の部分は手元の PHP のバージョンに合わせる）
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html

RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql

COPY --from=composer-builder /app/vendor ./vendor
COPY --from=node-builder /app/public/build ./public/build
COPY src/ .
RUN chown -R www-data:www-data storage bootstrap/cache

# 起動時: マイグレーション → 確認用データの投入 → storage のリンク → サーバー起動
CMD sh -c "php artisan migrate --force && php artisan db:seed --force && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"