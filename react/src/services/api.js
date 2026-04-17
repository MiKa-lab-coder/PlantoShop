// Point central pour la configuration de l'URL de l'API.
// Toutes les requêtes fetch utilisent cette constante — ne jamais écrire
// "http://localhost" directement dans les composants.
//
// La valeur est injectée par Vite au moment du build via la variable d'environnement
// VITE_API_URL définie dans react/.env (dev) ou par Coolify (prod).
export const API_URL = import.meta.env.VITE_API_URL || 'http://localhost';