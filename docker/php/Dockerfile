FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    curl \
    zip \
    unzip \
    git \
    nodejs \
    npm \
    linux-headers \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev

RUN docker-php-ext-install pdo_mysql mbstring pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
