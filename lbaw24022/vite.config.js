import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import dotenv from 'dotenv';

dotenv.config();
export default defineConfig({

    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/input.js', 'resources/js/bootstrap.js'],
            refresh: true,
        }),
    ],
    build: {
        manifest: true, // Ensure the manifest file is generated
        outDir: 'public/build', // Ensure this matches Laravel's expectations
      },
      server: {
        watch: {
          usePolling: true
        },
      },
});
