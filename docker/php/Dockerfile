# Utilise l'image de base PHP 8.4 FPM (Debian)
FROM php:8.4-fpm

# --- 1. Installation des dépendances système (Utilisation de APT) ---
RUN apt-get update && apt-get install -y \
    git \
    make \
    curl \
    # Clients de base de données nécessaires aux extensions PHP
    libpq-dev \
    libxml2-dev \
    # MongoDB n'a pas de client système "standard" nécessaire pour l'extension PECL,
    # mais nous ajoutons quelques outils généraux.
    wget \
    unzip \
    # Node.js et npm ne sont plus nécessaires ici car nous utilisons un service 'nodes' dédié,
    # mais je les conserve au cas où vous auriez besoin d'une exécution de script ponctuelle.
    nodejs \
    npm \
    bash \
    # Nettoyage du cache pour réduire la taille de l'image
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# --- 2. Installation des extensions PHP ---
# Installation des extensions de base (PDO pour MySQL et PostgreSQL)
# L'installation sur Debian se fait via les paquets APT pour plus de stabilité.
RUN docker-php-ext-install pdo_mysql pdo_pgsql \
    && docker-php-ext-enable pdo_pgsql

# Installation de l'extension MongoDB via PECL (méthode standard pour Debian)
# Note: L'extension 'mongodb' est séparée du client 'mongodb-tools'
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Installation d'OPcache (amélioration des performances)
RUN docker-php-ext-install opcache

# --- 3. Installation de Composer ---
# Télécharge et installe Composer globalement
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- 4. Configuration de l'utilisateur (Sécurité) ---
# Créer un utilisateur non-root pour plus de sécurité
ARG user=app
ARG uid=1000

# Utilisation de 'useradd -m' pour Debian au lieu de 'adduser -D' pour Alpine
RUN useradd -u ${uid} -m ${user}
RUN mkdir -p /var/www/html
RUN chown -R ${user}:${user} /var/www/html

# Switch vers l'utilisateur non-root pour les opérations suivantes
USER ${user}

# Définition du répertoire de travail (où le code sera monté)
WORKDIR /var/www/html

# --- 5. Configuration PHP personnalisée ---
# Copie du fichier php.ini (que nous allons créer ensuite)
COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

# Expose le port FPM (utilisé par Caddy en interne)
EXPOSE 9000
