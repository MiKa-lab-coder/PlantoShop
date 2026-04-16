FROM php:8.4-fpm

# install-php-extensions : binaires précompilés, beaucoup plus rapide que pecl
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

RUN apt-get update && apt-get install -y \
    git \
    curl \
    openssl \
    bash \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Extensions PHP
RUN install-php-extensions pdo_pgsql mongodb opcache

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

# prod settings baked in — dev overrides via volume mount in compose.yaml
COPY ./docker/php/php.prod.ini /usr/local/etc/php/conf.d/zz-app.ini

USER ${user}
WORKDIR /var/www/html

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]