#!/bin/bash
set -e

# ---------------------------------------------------------------------------
# Entrypoint du conteneur PHP-FPM
# Exécuté à chaque démarrage du conteneur, avant php-fpm.
# vendor/ et react/dist/ sont déjà présents (copiés au build Docker).
# ---------------------------------------------------------------------------

# --- 1. Scripts Composer post-install (skippés au build, exécutés ici) ---
echo "[entrypoint] Génération du cache Symfony..."
php bin/console cache:clear --no-warmup
php bin/console cache:warmup
echo "[entrypoint] Installation des assets Symfony..."
php bin/console assets:install public
echo "[entrypoint] Scripts post-install terminés."

# --- Fichiers statiques vers le volume partagé (lu par Caddy) ---
# Caddy ne peut pas monter l'image PHP — on exporte public/ et react/dist/
# vers /srv/static au démarrage, puis Caddy démarre une fois ce volume prêt.
echo "[entrypoint] Export des fichiers statiques vers /srv/static..."
mkdir -p /srv/static/public /srv/static/react/dist
cp -r /var/www/html/public/. /srv/static/public/
cp -r /var/www/html/react/dist/. /srv/static/react/dist/
echo "[entrypoint] Export terminé."

# --- 3. Clés JWT ---
if [ ! -f "config/jwt/private.pem" ]; then
    echo "[entrypoint] Clés JWT absentes — génération en cours..."
    mkdir -p config/jwt
    php bin/console lexik:jwt:generate-keypair --skip-if-exists
    echo "[entrypoint] Clés JWT générées dans config/jwt/."
fi

# --- 4. Migrations Doctrine ---
echo "[entrypoint] Lancement des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
echo "[entrypoint] Migrations terminées."

# --- Passe la main à la commande principale (php-fpm) ---
exec "$@"
