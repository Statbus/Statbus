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
    @composer

# Copy application
COPY . .

# RUN cp php.ini /usr/local/etc/php/conf.d/statbus.ini

# Install PHP dependencies
RUN composer install --prefer-dist --no-dev --no-progress --optimize-autoloader --ignore-platform-reqs

# Install and build frontend assets
RUN yarn install && yarn run build

# --- Stage 2 --- #
FROM dunglas/frankenphp:latest

ARG USER=www-data

WORKDIR /app

RUN install-php-extensions \
    pdo_mysql \
    gd \
    intl \
    zip \
    opcache \
    imagick

COPY --from=php-base /app /app

RUN \
	useradd ${USER}; \
	setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
	chown -R ${USER}:${USER} /config/caddy /data/caddy

USER ${USER}

RUN chown -R ${USER}:${USER} var vendor

ENV APP_ENV=prod

EXPOSE 443