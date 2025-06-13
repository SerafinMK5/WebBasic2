FROM php:8.1-apache

# Copiar el contenido del proyecto
COPY . /var/www/html/

# Instalar extensiones necesarias
RUN docker-php-ext-install mysqli

# Cambiar página de inicio a login.php
RUN echo "DirectoryIndex login.php" > /etc/apache2/conf-available/custom-directoryindex.conf \
    && a2enconf custom-directoryindex

EXPOSE 80
