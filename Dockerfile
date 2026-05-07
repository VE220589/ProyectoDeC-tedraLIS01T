FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libicu-dev \
        libpq-dev \
        unzip \
        zip \
    && docker-php-ext-install intl pdo_pgsql pgsql \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

COPY . .
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data writable \
    && chmod -R 775 writable

ENV CI_ENVIRONMENT=production

EXPOSE 80

