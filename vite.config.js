import { defineConfig } from 'vite';
import dotenv from 'dotenv';
import { fileURLToPath, URL } from 'node:url';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import vueDevTools from 'vite-plugin-vue-devtools';

dotenv.config();

const ROOT = new URL('.', import.meta.url);
const PUBLIC_DIR = process.env.PUBLIC_DIR?.trim() || 'public';
const BUILD_DIR = new URL(`./${PUBLIC_DIR}/build/`, ROOT);
const IS_PROD = process.env.APP_ENV === 'production';

export default defineConfig({
  server: {
    headers: { 'Access-Control-Allow-Origin': '*' },
  },
  cacheDir: '.vite',
  build: {
    outDir: fileURLToPath(BUILD_DIR),
    emptyOutDir: true,
    minify: IS_PROD,
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./resources/js', ROOT)),
      '@frontend': fileURLToPath(new URL('./resources/js/frontend', ROOT)),
      // '@rooty': fileURLToPath(new URL('./resources/js/rooty', ROOT)),
      '@editor': fileURLToPath(new URL('./resources/js/rooty/editor', ROOT)),
      '@icons': fileURLToPath(new URL('./resources/js/rooty/core/icons', ROOT)),
      // '@shared': fileURLToPath(new URL('./resources/js/shared', ROOT)),
      '@css': fileURLToPath(new URL('./resources/css', ROOT)),
      '@fonts': fileURLToPath(new URL('./resources/static/fonts', ROOT)),
      // '@wordpress/i18n': fileURLToPath(new URL('./resources/js/shims/wp-i18n.js', ROOT)),
    },
  },
  // optimizeDeps: {
  //   exclude: ['@wordpress/i18n'],
  // },
  plugins: [
    tailwindcss(),
    vue(),
    vueDevTools(),
    laravel({
      hmr: true,
      refresh: true,
      // refresh: ['resources/views/**/*.blade.php', 'app/**/*.php'],
      publicDirectory: PUBLIC_DIR,
      // hotFile: `${PUBLIC_DIR}/hot`,
      input: [
        'resources/js/frontend/app.js',
        'resources/js/rooty/editor/main.js',
      ],
    }),
    {
      name: 'php-hmr',
      handleHotUpdate({ file, server }) {
        if (file.endsWith('.php') || file.endsWith('.blade.php')) {
          server.ws.send({ type: 'full-reload', path: '*' });
        }
      },
    },
  ],
});
