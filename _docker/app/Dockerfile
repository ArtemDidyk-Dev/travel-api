FROM php:8.2-fpm-alpine

RUN apk --update --no-cache add \
        libpq \
        libpng \
        libjpeg-turbo \
        freetype \
        libzip \
        zip \
        unzip \
        git \
        libpq-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        libexif \
        libexif-dev \
        jpegoptim \
        optipng \
        pngquant \
        gifsicle \
        libwebp \
        nodejs \
        npm && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd && \
    docker-php-ext-install pdo pdo_mysql && \
    docker-php-ext-install bcmath && \
    docker-php-ext-install zip && \
    docker-php-ext-configure exif && \
    docker-php-ext-install exif && \
    rm -rf /var/cache/apk/* /tmp/* /var/tmp/*

# Встановлюємо svgo через npm
RUN npm install -g svgo

COPY ./_docker/app/php.ini /usr/local/etc/php/conf.d/php.ini

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

WORKDIR /var/www
