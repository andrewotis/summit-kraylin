FROM php:8.3-fpm

ARG WWWUSER=1000
ARG WWWGROUP=1000

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libgd-dev \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libxpm-dev \
    libfreetype6-dev \
    libkrb5-dev \
    libicu-dev \
    zip \
    unzip \
    supervisor \
    nano \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-webp --with-jpeg

RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    xml \
    zip \
    gd \
    bcmath \
    exif \
    pcntl \
    calendar \
    intl

RUN apt-get update && apt-get install -y libmagickwand-dev --no-install-recommends && rm -rf /var/lib/apt/lists/*

RUN pecl install redis imagick && docker-php-ext-enable redis imagick

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN groupadd --force -g $WWWGROUP sail
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u $WWWUSER sail

RUN mkdir -p /var/www/html/storage/framework/{cache,sessions,testing,views} /var/www/html/storage/logs /var/www/html/bootstrap/cache && chown -R sail:sail /var/www/html

COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/startup.sh /usr/local/bin/startup.sh

WORKDIR /var/www/html

CMD ["/usr/local/bin/startup.sh"]
