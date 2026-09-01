FROM php:8.2-apache

# Instalar dependencias del sistema y extensiones PHP para PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev zip unzip git \
    && docker-php-ext-install pdo pdo_pgsql \
    && a2enmod rewrite

# Configurar la raíz de Apache hacia la carpeta public de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copiar el código del backend
COPY . /var/www/html

# Instalar Composer e instalar dependencias
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Dar permisos al servidor sobre las carpetas de caché
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache