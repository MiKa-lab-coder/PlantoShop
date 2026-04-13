#!/bin/bash
set -e

# ---------------------------------------------------------------------------
# Entrypoint du conteneur PHP-FPM
# Exécuté à chaque démarrage du conteneur, avant php-fpm.
# ---------------------------------------------------------------------------

# --- 1. Dépendances Composer ---
# En dev, vendor/ est souvent absent du clone (gitignored).
# On installe automatiquement pour ne pas avoir à le faire manuellement.
if [ ! -d "vendor" ]; then
    echo "[entrypoint] vendor/ absent — lancement de composer install..."
    if [ "${APP_ENV}" = "prod" ]; then
        composer install --no-dev --optimize-autoloader --no-interaction --no-progress
    else
        composer install --no-interaction --no-progress
    fi
    echo "[entrypoint] composer install terminé."
fi

# --- 2. Clés JWT ---
# Si les clés n'existent pas (premier démarrage ou nouveau clone),
# on les génère via la commande Symfony du bundle Lexik.
if [ ! -f "config/jwt/private.pem" ]; then
    echo "[entrypoint] Clés JWT absentes — génération en cours..."
    mkdir -p config/jwt
    php bin/console lexik:jwt:generate-keypair --skip-if-exists
    echo "[entrypoint] Clés JWT générées dans config/jwt/."
fi

# --- Passe la main à la commande principale (php-fpm) ---
exec "$@"