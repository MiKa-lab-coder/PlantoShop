FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    make \
    curl \  \
    libpq-dev \
    libxml2-dev \
    wget \
    unzip \
    nodejs \
    npm \
    bash \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Installation des extensions de base (PDO pour MySQL et PostgreSQL)
RUN docker-php-ext-install pdo_mysql pdo_pgsql \
    && docker-php-ext-enable pdo_pgsql

# Installation de l'extension MongoDB via PECL (méthode standard pour Debian)

RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Installation d'OPcache pour améliorer les performances
RUN docker-php-ext-install opcache


#  installation de Composer globalement
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer un utilisateur non-root pour exécuter l'application
ARG user=app
ARG uid=1000

# Utilisation de l'argument pour créer l'utilisateur
RUN useradd -u ${uid} -m ${user}
RUN mkdir -p /var/www/html
RUN chown -R ${user}:${user} /var/www/html

# Switch vers l'utilisateur non-root pour les opérations suivantes
USER ${user}

# Définition du répertoire de travail
WORKDIR /var/www/html

# Copie du fichier de configuration PHP personnalisé
COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

# Expose le port FPM (utilisé par Caddy en interne)
EXPOSE 9000
