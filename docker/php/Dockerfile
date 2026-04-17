# ============================================================
# Stage 1 : Build React (Vite)
# ============================================================
FROM node:20-alpine AS react_builder

ARG VITE_API_URL
ENV VITE_API_URL=${VITE_API_URL}

WORKDIR /app/react
COPY react/package.json ./
RUN npm install
COPY react/ ./
RUN npm run build

# ============================================================
# Stage 2 : PHP-FPM + code applicatif complet
# ============================================================
FROM php:8.4-fpm

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

RUN apt-get update && apt-get install -y \
    git \
    curl \
    openssl \
    bash \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions pdo_pgsql mongodb opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ARG user=app
ARG uid=1000
RUN useradd -u ${uid} -m ${user} \
    && mkdir -p /var/www/html \
    && chown -R ${user}:${user} /var/www/html

COPY --chmod=755 ./docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY ./docker/php/php.prod.ini /usr/local/etc/php/conf.d/zz-app.ini

USER ${user}
WORKDIR /var/www/html

# Code applicatif (vendor/, react/node_modules/ et react/dist/ exclus via .dockerignore)
COPY --chown=${user}:${user} . .

# react/dist/ compilé depuis le stage Node
COPY --from=react_builder --chown=${user}:${user} /app/react/dist ./react/dist

# .env minimal requis par Symfony bootEnv (cherche ce fichier même si APP_ENV est déjà défini)
# Les vraies valeurs sont injectées par Docker Compose au runtime et écrasent ce fichier.
RUN echo "APP_ENV=prod" > .env

# Dépendances Composer au build — sans scripts post-install (pas de .env complet au build)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
