import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      '/api': {
        target: 'http://kidposture-api.test',
        changeOrigin: true,
      },
      '/sanctum': {
        target: 'http://kidposture-api.test',
        changeOrigin: true,
      }
    }
  }
})
