#!/bin/bash
set -e

# ---------------------------------------------------------------------------
# Entrypoint du conteneur PHP-FPM
# vendor/ et react/dist/ sont déjà présents (copiés au build Docker).
# ---------------------------------------------------------------------------

# --- 1. Clés JWT (openssl direct — pas de boot Symfony) ---
# Doit être fait AVANT tout appel à php bin/console car LexikJWT valide
# l'existence des clés à la compilation du container DI.
if [ ! -f "config/jwt/private.pem" ]; then
    echo "[entrypoint] Génération des clés JWT..."
    mkdir -p config/jwt
    openssl genrsa -out config/jwt/private.pem 4096
    openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
    chmod 644 config/jwt/private.pem config/jwt/public.pem
    echo "[entrypoint] Clés JWT générées."
fi

# --- 2. Migrations Doctrine ---
echo "[entrypoint] Migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
echo "[entrypoint] Migrations terminées."

# --- 3. Permissions var/ pour PHP-FPM (www-data) ---
# Les commandes console ci-dessus tournent en root → cache créé en root.
# PHP-FPM tourne en www-data → doit pouvoir écrire dans var/.
mkdir -p /var/www/html/var/cache /var/www/html/var/log
chown -R www-data:www-data /var/www/html/var

# --- 4. Fichiers statiques → volume partagé (lu par Caddy) ---
echo "[entrypoint] Export vers /srv/static..."
mkdir -p /srv/static/public /srv/static/react/dist
cp -r /var/www/html/public/. /srv/static/public/
cp -r /var/www/html/react/dist/. /srv/static/react/dist/
echo "[entrypoint] Export terminé."

# --- Passe la main à php-fpm ---
exec "$@"
