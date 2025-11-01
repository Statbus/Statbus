# --- Stage 0: Base PHP image with system deps --- #
FROM dunglas/frankenphp:latest AS php-base

# Install system packages & Node.js
RUN apt-get update && apt-get install -y \
    curl \
    gnupg \
    && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && corepack enable \
    && corepack prepare yarn@stable --activate \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (cached unless Dockerfile changes)
RUN install-php-extensions \
    pdo_mysql gd intl zip opcache imagick

# Install Composer
RUN install-php-extensions @composer

# Copy only configuration files that rarely change
COPY ./deploy/statbus.caddyfile /etc/frankenphp/caddy.d/statbus.caddyfile
COPY ./deploy/php.ini-production /usr/local/etc/php/php.ini-production
COPY ./deploy/php.ini-statbus /usr/local/etc/php/conf.d/php.ini-statbus

# --- Stage 1: Application --- #
FROM php-base AS app

WORKDIR /app

# Copy only Composer & Yarn metadata first for caching
COPY composer.json composer.lock ./
COPY package.json yarn.lock ./

# Install PHP & Node dependencies
RUN composer install --prefer-dist --no-dev --no-progress --optimize-autoloader --ignore-platform-reqs --no-scripts
RUN yarn install

# Now copy the rest of the app
COPY . .

# Build frontend assets
RUN yarn run build

# Set permissions (optional, for FrankenPHP)
# ARG USER=www-data
# RUN setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
#     chown -R ${USER}:${USER}  vendor /config/caddy /data/caddy

ENV APP_ENV=prod
EXPOSE 443
