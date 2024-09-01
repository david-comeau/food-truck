FROM php:8.3-cli

# install deps
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    zip \
    curl

# install php exts
RUN docker-php-ext-install pdo pdo_mysql zip

# install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# install symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# set working dir
WORKDIR /app

# copy project into container
COPY . .

# set dev environment to allow tests in Docker
ENV APP_ENV=dev

# install project deps
RUN composer install --optimize-autoloader

# clear & warm symfony cache
RUN set -eux; \
    mkdir -p var/cache var/log; \
    php bin/console cache:clear --no-warmup; \
    php bin/console cache:warmup; \
    chown -R www-data:www-data var

# dump optimized composer autoload files
RUN composer dump-autoload --optimize --classmap-authoritative

# set entrypoint
ENTRYPOINT ["php", "bin/console"]