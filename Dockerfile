# Utiliser l’image officielle PHP avec Apache
FROM php:8.2-apache

# Installer les extensions PHP nécessaires (PDO et mysqli)
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Installer l’extension MongoDB via PECL pour version MongoDB locale 
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Copier la configuration Apache locale
COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Activer mod_rewrite pour l’URL rewriting MVC
RUN a2enmod rewrite

# Copier tout le projet dans /var/www/html (racine web Apache)
COPY . /var/www/html

# Changer les permissions (Apache = www-data)
RUN chown -R www-data:www-data /var/www/html