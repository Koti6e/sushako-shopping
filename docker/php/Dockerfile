FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    git \
    curl \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    zip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

RUN docker-php-ext-install \
    bcmath \
    exif \
    gd \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo_mysql \
    zip

RUN pecl install redis \
    && docker-php-ext-enable redis

# Do not disclose the PHP runtime in HTTP response headers.
RUN printf "expose_php=Off\n" > /usr/local/etc/php/conf.d/zz-security.ini

COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php/entrypoint.sh /usr/local/bin/sushako-entrypoint
RUN chmod +x /usr/local/bin/sushako-entrypoint

ENTRYPOINT ["sushako-entrypoint"]
CMD ["php-fpm"]
