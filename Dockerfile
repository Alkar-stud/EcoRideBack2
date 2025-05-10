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
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl zip pdo pdo_mysql gd \
    && a2enmod rewrite \
    && a2enmod ssl \
    && a2enmod headers

# Installation de l'extension MongoDB pour PHP
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définit le répertoire de travail
WORKDIR /var/www/html

# Monter le secret et installer les dépendances
#RUN --mount=type=secret,id=.env.docker \
#    npm install --config /run/secrets/config \


# Copie les fichiers de l'application
COPY . .

# Donne les permissions nécessaires
RUN chown -R www-data:www-data /var/www/html

# Configure Apache pour utiliser le répertoire public comme racine
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Ajoute un ServerName global à Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Définit les permissions pour le cache et les logs
RUN mkdir -p var/cache var/log && chown -R www-data:www-data var

USER www-data
# Installe les dépendances Symfony sans exécuter les scripts pour fly.io
RUN composer install --no-scripts

RUN composer update
RUN composer dump-autoload

USER root
# Expose le port 8080 car le port 80 peut être déjà utilisé sur la machine hôte
EXPOSE 8080
# Commande par défaut
CMD ["apache2-foreground"]
