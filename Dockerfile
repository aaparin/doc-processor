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
COPY composer.json composer.lock ./

# Установка зависимостей
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Копирование остальных файлов проекта
COPY . .

# Установка переменных окружения
ENV MESSENGER_TRANSPORT_DSN=sync://
ENV APP_ENV=prod
ENV CONVERSION_ENDPOINT=http://localhost:8181/convert

# Финальные настройки
RUN composer dump-autoload --optimize \
    && mkdir -p var/cache var/log var/data \
    && chmod -R 777 var/cache var/log var/data \
    && touch var/data/data.db \
    && php bin/console doctrine:schema:update --force \
    && php bin/console cache:clear --env=prod

# Очистка
RUN rm -rf /tmp/* /var/cache/apk/*

# Порт
EXPOSE 8000

# Запуск symfony сервера
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]