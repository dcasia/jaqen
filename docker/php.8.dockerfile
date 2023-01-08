FROM php:8.0-fpm-alpine

RUN apk add --no-cache curl git libzip-dev libpng-dev jpeg-dev

RUN docker-php-ext-configure gd --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd

RUN curl -sS https://install.phpcomposer.com/installer | php && \
    mv composer.phar /usr/local/bin/composer

RUN addgroup --gid 1000 composer && \
    adduser --disabled-password --ingroup composer --uid 1000 composer

USER composer

CMD ["php-fpm"]
