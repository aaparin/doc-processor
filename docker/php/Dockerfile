FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev \
    librabbitmq-dev \
    libssl-dev \
    pkg-config \
    # Dependencies for GD
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install AMQP PHP extension
RUN pecl install amqp && \
    docker-php-ext-enable amqp

# Configure and install GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd

# Install PHP extensions
RUN docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u 1000 -d /home/dev dev
RUN mkdir -p /home/dev/.composer && \
    chown -R dev:dev /home/dev

# Create SQLite directory and database file with proper permissions
RUN mkdir -p /var/www/var/data \
    && touch /var/www/var/data/database.db \
    && chown -R dev:www-data /var/www/var \
    && chmod -R 775 /var/www/var

# Set working directory
WORKDIR /var/www

USER dev