FROM php:8.4-fpm

ARG UID=1000
ARG GID=1000

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git \
        unzip \
        nginx \
        supervisor \
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
    rm -f /etc/nginx/sites-enabled/default; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY services/configs/php/app.dev.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY services/configs/php/opcache.dev.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY services/configs/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY services/configs/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN usermod -u ${UID} www-data \
  && groupmod -g ${GID} www-data

WORKDIR /var/www

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
