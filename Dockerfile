# Imagen base oficial con Apache y PHP
FROM php:8.1-apache

# Copiar todos los archivos del proyecto al directorio web
COPY . /var/www/html/

# Instalar extensiones necesarias (mysqli por ejemplo)
RUN docker-php-ext-install mysqli

# Puerto que expondrá el contenedor (Render espera 80)
EXPOSE 80