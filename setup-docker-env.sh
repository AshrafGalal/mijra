#!/bin/bash

set -e

PROJECT_ROOT=$(pwd)

echo "🚀 Setting up Docker structure for Laravel project at $PROJECT_ROOT"

# Create folder structure
mkdir -p docker/php docker/nginx docker/horizon

# Create PHP Dockerfile
cat > docker/php/Dockerfile <<'EOF'
FROM php:8.3-fpm

WORKDIR /var/www/html

# Install system dependencies first (these change rarely)
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

# Install PHP extensions (depends on libs above)
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Install Redis via PECL (separate so cache works better)
RUN pecl install redis \
    && docker-php-ext-enable redis


FROM php:8.1-fpm

WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    supervisor \
    && docker-php-ext-install pdo_mysql zip \
    && pecl install redis && docker-php-ext-enable redis

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy application code
COPY . .

# Install dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port
EXPOSE 9000
EOF

# Create Nginx config
cat > docker/nginx/default.conf <<'EOF'
server {
    listen 80;
    index index.php index.html;
    server_name localhost;

    root /var/www/html/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

# Horizon Supervisor config
cat > docker/horizon/supervisord.conf <<'EOF'
[supervisord]
nodaemon=true
logfile=/var/www/html/storage/logs/supervisord.log
pidfile=/var/run/supervisord.pid

[include]
files = /etc/supervisor/conf.d/*.conf
EOF

# Create docker-compose.yml with Sail-style port forwarding
cat > docker-compose.yml <<EOF
services:
    app:
        build:
            context: ./docker/php
        container_name: laravel-app
        volumes:
            - ./:/var/www/html
            - ./storage:/var/www/html/storage
            - ./logs:/var/www/html/storage/logs
        networks:
            - laravel
        depends_on:
            - mysql
            - redis
        #      - meilisearch
        restart: unless-stopped

    nginx:
        image: nginx:stable
        container_name: laravel-nginx
        ports:
            - "${FORWARD_WEB_PORT:-80}:80"
            - "443:443"
        volumes:
            - ./:/var/www/html
            - ./storage:/var/www/html/storage
            - ./logs:/var/www/html/storage/logs
        depends_on:
            - app
        networks:
            - laravel
        restart: unless-stopped

    mysql:
        image: mysql:8.0
        container_name: laravel-mysql
        environment:
            MYSQL_ROOT_PASSWORD: "${DB_PASSWORD}"
            MYSQL_ROOT_HOST: "%"
            MYSQL_DATABASE: "${DB_DATABASE}"
            MYSQL_PASSWORD: "${DB_PASSWORD}"
        ports:
            - "${FORWARD_DB_PORT:-3306}:3306"
        volumes:
            - dbdata:/var/lib/mysql
        networks:
            - laravel
        restart: unless-stopped

    redis:
        image: redis:alpine
        container_name: laravel-redis
        volumes:
            - redis_data:/data
        networks:
            - laravel
        restart: unless-stopped
    #  meilisearch:
    #    image: getmeili/meilisearch:v1.11
    #    container_name: laravel-meilisearch
    #    environment:
    #      MEILI_NO_ANALYTICS: "true"
    #    volumes:
    #      - meilidata:/meili_data
    #    networks:
    #      - laravel

    #  node:
    #    image: node:20-alpine
    #    container_name: laravel-node
    #    working_dir: /var/www/html
    #    volumes:
    #      - ./:/var/www/html
    #    command: sh -c "npm install"
    #    networks:
    #      - laravel

    horizon:
        build:
            context: ./docker/php
        container_name: laravel-horizon
        volumes:
            - ./:/var/www/html
            - ./docker/horizon/supervisord.conf:/etc/supervisord.conf
            - ./docker/horizon/horizon.conf:/etc/supervisor/conf.d/horizon.conf
        command: /usr/bin/supervisord -c /etc/supervisord.conf
        depends_on:
            - redis
            - mysql
        networks:
            - laravel
        restart: unless-stopped

volumes:
    dbdata:
    meilidata:
    redis_data:

networks:
    laravel:
        driver: bridge
EOF

# Create storage & logs folders if not exist
mkdir -p storage logs

echo "✅ Docker files created successfully!"
echo "Next steps:"
echo "1. Run: docker-compose build --no-cache"
echo "2. Run: docker-compose up -d"
echo "3. Laravel app will be available at: http://localhost:\${FORWARD_WEB_PORT:-8080}"
echo "4. MySQL available on: 127.0.0.1:\${FORWARD_DB_PORT:-3306} (user: laravel / pass: laravel / db: laravel)"
echo ""
echo "You can override the default ports by setting environment variables:"
echo "export FORWARD_WEB_PORT=8000"
echo "export FORWARD_DB_PORT=3307"
echo "Then run docker-compose up again"
