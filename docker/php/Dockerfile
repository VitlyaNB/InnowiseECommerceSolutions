FROM php:8.5-fpm-alpine

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
RUN printf "memory_limit=1024M\n" > /usr/local/etc/php/conf.d/zz-memory-limit.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
