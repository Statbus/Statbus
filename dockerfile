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

# Install PHP dependencies
RUN composer install --prefer-dist --no-dev --no-progress --optimize-autoloader --ignore-platform-reqs

# Install and build frontend assets
RUN yarn install && yarn build

# --- Stage 2 --- #
FROM dunglas/frankenphp:latest

WORKDIR /app

RUN install-php-extensions \
    pdo_mysql \
    gd \
    intl \
    zip \
    opcache \
    imagick

COPY --from=php-base /app /app

RUN chmod -R 777 var vendor

ENV APP_ENV=prod

EXPOSE 80