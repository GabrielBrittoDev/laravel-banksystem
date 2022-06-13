FROM php:8.0-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    sqlite3 \
    libsqlite3-dev \
    wget \
    nano \
    zip \
    unzip \
    sudo

RUN docker-php-ext-install  pdo mbstring pdo_mysql gd

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN pecl install redis \
    && docker-php-ext-enable redis

RUN mkdir -p /home/xdebug

COPY ./docker/php/xdebug/xdebug-debug.ini /home/xdebug/xdebug-debug.ini
COPY ./docker/php/xdebug/xdebug-default.ini /home/xdebug/xdebug-default.ini
COPY ./docker/php/xdebug/xdebug-off.ini /home/xdebug/xdebug-off.ini
COPY ./docker/php/xdebug/xdebug-profile.ini /home/xdebug/xdebug-profile.ini
COPY ./docker/php/xdebug/xdebug-trace.ini /home/xdebug/xdebug-trace.ini

ARG USER=www
ARG UID=1000
ARG GID=1000
ARG PW=docker

RUN echo "%${USER} ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

RUN useradd -m ${USER} --uid=${UID} && echo "${USER}:${PW}" | \
    chpasswd
USER ${USER}

ENTRYPOINT ["bash", "/var/www/docker/php/script/entrypoint.sh"]
