# --- Stage 0: PHP Extensions + Composer --- #
FROM dunglas/frankenphp:latest AS php-exts

# Install PHP extensions + Composer (cached unless list changes)
RUN install-php-extensions \
    pdo_mysql \
    gd \
    intl \
    zip \
    opcache \
    imagick \
    @composer


# --- Stage 1: Build (with Node + Yarn) --- #
FROM php-exts AS build

# Install Node & Yarn (for frontend build)
RUN apt-get update && apt-get install -y \
    curl \
    gnupg \
 && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
 && apt-get install -y nodejs \
 && corepack enable \
 && corepack prepare yarn@stable --activate \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Cache Composer dependencies
COPY composer.json composer.lock ./
RUN composer install \
    --prefer-dist --no-dev --no-progress --optimize-autoloader --ignore-platform-reqs

# Cache Yarn dependencies
COPY package.json yarn.lock ./
RUN yarn install --frozen-lockfile

# Copy the rest of the source and build assets
COPY . .
RUN yarn run build


# --- Stage 2: Runtime --- #
FROM php-exts AS runtime

WORKDIR /app

# Copy built application from build stage
COPY --from=build /app /app

# Allow FrankenPHP to bind to 443 without root
RUN setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
 && chown -R www-data:www-data /config/caddy /data/caddy /app/var /app/vendor

ENV APP_ENV=prod

EXPOSE 443

USER www-data
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]