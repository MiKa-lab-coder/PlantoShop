FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    make \
    curl \
    libpq-dev \
    libxml2-dev \
    wget \
    unzip \
    openssl \
    bash \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Extensions PDO (PostgreSQL)
RUN docker-php-ext-install pdo_mysql pdo_pgsql \
    && docker-php-ext-enable pdo_pgsql

# Extension MongoDB
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# OPcache
RUN docker-php-ext-install opcache

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Utilisateur non-root
ARG user=app
ARG uid=1000
RUN useradd -u ${uid} -m ${user} \
    && mkdir -p /var/www/html \
    && chown -R ${user}:${user} /var/www/html

# Entrypoint (copié en root pour pouvoir écrire dans /usr/local/bin, puis rendu exécutable)
COPY --chmod=755 ./docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh

# Configuration PHP (dev par défaut ; remplacée par php.prod.ini via volume en prod)
COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

USER ${user}
WORKDIR /var/www/html

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]