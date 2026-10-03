FROM php:8.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl libcurl4-openssl-dev libfreetype6-dev libicu-dev libjpeg62-turbo-dev libonig-dev libpng-dev libxml2-dev libzip-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" dom gd intl mbstring pdo_mysql xml zip opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
ENV APP_ENV=prod APP_DEBUG=0
COPY SYMFONY/composer.json SYMFONY/composer.lock ./
RUN php -m && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts
COPY SYMFONY/ .
RUN composer run-script post-install-cmd --no-interaction \
    && mkdir -p var/cache var/log public/uploads \
    && chown -R www-data:www-data var public/uploads

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri "s!/var/www/!${APACHE_DOCUMENT_ROOT}/!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl --fail http://127.0.0.1/ || exit 1

EXPOSE 80
CMD ["apache2-foreground"]
