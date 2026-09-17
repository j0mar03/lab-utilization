FROM php:8.3-fpm

# System dependencies (matches faculty portfolio)
RUN apt-get update && apt-get install -y \
    git curl zip unzip rsync libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
ENV COMPOSER_HOME=/tmp/composer-cache
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node 20 (for Vite / Breeze asset building)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

WORKDIR /var/www/html

# Copy entrypoint and make executable
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
