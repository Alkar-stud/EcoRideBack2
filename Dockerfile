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
    && docker-php-ext-install intl zip pdo pdo_mysql \
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

# Installe les dépendances Symfony
RUN composer install

# Définit les permissions pour le cache et les logs
RUN mkdir -p var/cache var/log && chown -R www-data:www-data var

# Expose le port 8080
EXPOSE 8080

# Commande par défaut
CMD ["apache2-foreground"]