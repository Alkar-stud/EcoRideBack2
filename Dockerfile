# Utilise l'image officielle PHP avec Apache
FROM php:8.2-apache

# Installe les extensions nécessaires pour Symfony
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    unzip \
    git \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl zip pdo pdo_mysql gd \
    && a2enmod rewrite

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définit le répertoire de travail
WORKDIR /var/www/html

# Copie les fichiers de l'application
COPY . .

# Donne les permissions nécessaires
RUN chown -R www-data:www-data /var/www/html

# Configure Apache pour utiliser le répertoire public comme racine
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Ajoute un ServerName global à Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Installe les dépendances Symfony sans exécuter les scripts pour fly.io
RUN composer install --no-scripts

# Définit les permissions pour le cache et les logs
RUN mkdir -p var/cache var/log && chown -R www-data:www-data var

RUN composer update
RUN composer dump-autoload

# Expose le port 8080
EXPOSE 8080
# Expose le port 80 pour fly.io
#EXPOSE 80
# Commande par défaut
CMD ["apache2-foreground"]

USER www-data
#RUN  php bin/console doctrine:migrations:migrate --no-interaction
USER root