FROM php:8.3-apache

USER root
RUN mkdir /var/www/site && apt-get update -y \
&& apt-get install wget g++ autoconf make re2c pipx libjpeg62-turbo libwebp7 libwebpdemux2 libwebpmux3 libxslt1.1 libglib2.0-0 libpango-1.0-0 libpangoft2-1.0-0 libmagickwand-6.q16-6 libmagickcore-6.q16-6 librabbitmq4 libicu-dev libxslt-dev libxml2-dev libfreetype6-dev libjpeg62-turbo-dev libwebp-dev libpng-dev libmagickwand-dev librabbitmq-dev -y \
&& docker-php-ext-configure intl \
&& docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
&& docker-php-ext-install -j$(nproc) intl xsl calendar gd \
&& pecl install mailparse xdebug amqp \
&& docker-php-ext-enable mailparse amqp \
&& mkdir -p /usr/src/php/ext/imagick \
# Locking on specific commit hash to provide consistent results, at the moment of writing this is the HEAD of master
&& curl -fsSL https://github.com/Imagick/imagick/archive/28f27044e435a2b203e32675e942eb8de620ee58.tar.gz | tar xvz -C "/usr/src/php/ext/imagick" --strip 1 \
&& docker-php-ext-install imagick \
# End install Imagick from source
&& PIPX_HOME=/opt/pipx PIPX_BIN_DIR=/usr/local/bin pipx install weasyprint \
&& apt-get autoremove -y && apt-get clean \
&& chown -R www-data:www-data /var/www/site && a2enmod rewrite

WORKDIR /var/www/site

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer