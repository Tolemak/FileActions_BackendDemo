FROM php:8.3-apache

# Only what this app actually needs: Imagick for image processing, zip for
# Composer, and Xdebug for step debugging (see ../../.vscode/launch.json).
RUN apt-get update && apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS \
        libmagickwand-dev \
        libzip-dev \
        unzip \
    && pecl install imagick xdebug \
    && docker-php-ext-enable imagick xdebug \
    && docker-php-ext-install -j"$(nproc)" zip \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite \
    && mkdir -p /var/www/site \
    && chown -R www-data:www-data /var/www/site

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/site
