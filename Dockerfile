FROM php:8.3-cli-alpine

# Установка необходимых расширений PHP
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    libzip-dev \
    icu-dev \
    sqlite-dev \
    && docker-php-ext-install zip intl opcache pdo_sqlite

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка PHP
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Создание рабочей директории
WORKDIR /app

# Копирование composer файлов
COPY composer.json ./

# Установка symfony/runtime (явно)
RUN composer require symfony/runtime

# Установка зависимостей без выполнения скриптов
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Копирование остальных файлов проекта
COPY . .

# Копирование файла .env
COPY .env /app/.env

# Выполнение скриптов
RUN composer run-script auto-scripts

# Финальные настройки
RUN composer dump-autoload --optimize \
    && mkdir -p var/cache var/log var/data \
    && chmod -R 777 var/cache var/log var/data \
    && touch var/data/data.db \
    && export MESSENGER_TRANSPORT_DSN=sync:// \
    && php bin/console doctrine:schema:update --force \
    && php bin/console cache:clear --env=prod

# Порт
EXPOSE 8000

# Запуск symfony сервера
CMD php -S 0.0.0.0:8000 -t public
