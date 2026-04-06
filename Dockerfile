FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mysqli gd

RUN sed -i 's/^LoadModule mpm_event/# LoadModule mpm_event/' /etc/apache2/mods-enabled/mpm_event.conf 2>/dev/null || true \
    && a2enmod mpm_prefork rewrite

WORKDIR /var/www/html
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80