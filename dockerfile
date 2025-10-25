# --- Stage 1 --- #
FROM node:18-bullseye AS build
FROM dunglas/frankenphp:latest AS php-base

ENV APP_ENV=prod

RUN apt-get update && apt-get install -y \
    curl \
    gnupg \
    && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && corepack enable \
    && corepack prepare yarn@stable --activate \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    pdo_mysql \
    gd \
    intl \
    zip \
    opcache \
    imagick

RUN install-php-extensions \
    @composer

# Copy application
COPY ./deploy .

RUN mkdir /etc/frankenphp/caddy.d/
RUN cp /app/statbus.caddyfile /etc/frankenphp/caddy.d/statbus.caddyfile

RUN cp /app/php.ini-production /usr/local/etc/php/php.ini-production
RUN cp /app/php.ini-statbus /usr/local/etc/php/conf.d/php.ini-statbus

# --- Stage 2 --- #
FROM php-base

ARG USER=www-data

WORKDIR /app

COPY . .

# Install PHP dependencies
RUN composer install --prefer-dist --no-dev --no-progress --optimize-autoloader --ignore-platform-reqs

# Install and build frontend assets
RUN yarn install && yarn run build

RUN setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
	chown -R ${USER}:${USER} /config/caddy /data/caddy

RUN chown -R ${USER}:${USER} var vendor

ENV APP_ENV=prod

EXPOSE 443