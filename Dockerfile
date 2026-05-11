FROM php:8.3-apache

# Instala dependencias del sistema y extensiones PHP necesarias para CodeIgniter y PostgreSQL.
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

# Composer se copia desde la imagen oficial para instalar dependencias PHP.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Primero se copian los archivos de Composer para aprovechar cache de Docker.
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

# Copia el codigo de la aplicacion y configura Apache para servir desde public/.
COPY . .
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# writable debe quedar disponible para logs, cache y sesiones de CodeIgniter.
RUN chown -R www-data:www-data writable \
    && chmod -R 775 writable

ENV CI_ENVIRONMENT=production

# Apache escucha dentro del contenedor en el puerto 80.
EXPOSE 80
