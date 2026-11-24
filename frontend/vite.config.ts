import react from '@vitejs/plugin-react'
import { defineConfig } from 'vitest/config'

// The browser only ever talks to this dev server. Requests to /api are
// forwarded to the Laravel backend, so no product data is fetched directly
// from a third party and no CORS setup is needed.
export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      '/api': {
        target: process.env.BACKEND_URL ?? 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
  test: {
    environment: 'jsdom',
    setupFiles: ['./src/test-setup.ts'],
  },
})
