import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { VitePWA } from 'vite-plugin-pwa'
import path from 'path'

export default defineConfig({
  plugins: [react(), VitePWA({
    registerType: 'autoUpdate',
    includeAssets: ['favicon.svg'],
    manifest: {
      name: 'ARMORA ERP — Portal Cliente',
      short_name: 'ARMORA',
      description: 'Portal de cliente para ARMORA SAC — ERP de facturación electrónica',
      theme_color: '#1565c0',
      background_color: '#fafafa',
      display: 'standalone',
      orientation: 'portrait-primary',
      start_url: '/portal/productos',
      icons: [
        { src: '/favicon.svg', sizes: 'any', type: 'image/svg+xml', purpose: 'any' },
      ],
    },
    workbox: {
      globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
    },
  })],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    port: 5175,
    proxy: {
      '/api': {
        target: 'http://localhost:8005',
        changeOrigin: true,
      },
    },
  },
  optimizeDeps: {
    exclude: ['@popperjs/core', '@hookform/resolvers/zod'],
  },
})
