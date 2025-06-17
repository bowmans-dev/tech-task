import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/calendar/calendar.js'],
      refresh: true,
    }),
    tailwindcss(),
  ],
  server: {
    host: '0.0.0.0', // So mobile usb debugging can access this machine’s IP
    port: 5173,
    strictPort: true,
    origin: 'http://localhost:5173', // tell Laravel where to expect Vite assets
    cors: true,
    hmr: {
      host: 'localhost',
    },
  },
});