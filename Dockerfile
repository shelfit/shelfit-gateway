FROM php:8.3-fpm

ARG UID=1000
ARG GID=1000

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        librabbitmq-dev \
        libssl-dev \
    ; \
    docker-php-ext-configure intl; \
    docker-php-ext-install -j"$(nproc)" \
        intl \
        opcache \
        pdo_mysql \
        zip \
        sockets \
    ; \
    pecl install amqp; \
    docker-php-ext-enable amqp; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY .infra/php/app.dev.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY .infra/php/opcache.dev.ini /usr/local/etc/php/conf.d/zz-opcache.ini

RUN usermod -u ${UID} www-data \
  && groupmod -g ${GID} www-data

WORKDIR /var/www
USER www-data

EXPOSE 9000
CMD ["php-fpm"]
