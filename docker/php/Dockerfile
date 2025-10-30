FROM php:8.3-fpm

WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Install Redis via PECL
RUN pecl install redis && docker-php-ext-enable redis

# Install Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy only composer files first
COPY composer.json composer.lock ./

# Install dependencies without autoload generation
RUN composer install --no-interaction --no-autoloader --no-dev

# Copy the rest of the application code
COPY --chown=www-data:www-data . .

# Generate optimized autoload files
RUN composer dump-autoload --optimize

# Set permissions for storage/cache
RUN chmod -R 775 storage bootstrap/cache


COPY docker/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Expose port
EXPOSE 9000
