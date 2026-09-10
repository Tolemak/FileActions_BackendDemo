FROM php:8.3-apache

# Only what this app actually needs: Imagick for image processing, zip for
# Composer, and Xdebug for step debugging (see ../../.vscode/launch.json).
# libmagickwand-7.q16-10 and libgomp1 are runtime deps imagick.so links
# against; they aren't pulled in as hard deps of libmagickwand-dev/gcc, so
# without listing them explicitly the purge below removes them and imagick
# fails to load at runtime.
RUN apt-get update && apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS \
        libmagickwand-dev \
        libmagickwand-7.q16-10 \
        libgomp1 \
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

# Denies every coder but JPEG/PNG/GIF and all delegates, which is what closes
# the ImageTragick family (MSL/MVG/SVG/URL fetch) rather than relying on the
# upload check alone.
COPY imagemagick-policy.xml /etc/ImageMagick-7/policy.xml

WORKDIR /var/www/site
