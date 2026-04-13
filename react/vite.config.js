import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react-swc'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  // Vite expose automatiquement toutes les variables préfixées VITE_ via import.meta.env.
  // VITE_API_URL est définie dans react/.env (dev) ou injectée par Coolify (prod).
})