FROM php:8.4-fpm

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    curl \
    ca-certificates \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl intl \
    && curl -sL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Limpiar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www
