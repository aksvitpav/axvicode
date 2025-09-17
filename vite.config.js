import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fs from 'fs';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        https: {
            key: fs.readFileSync('/app/docker/traefik/certs/axvicode.test.key'),
            cert: fs.readFileSync('/app/docker/traefik/certs/axvicode.test.crt'),
        },
        origin: 'https://axvicode.test:5173',
        cors: {
            origin: 'https://axvicode.test',
            credentials: true,
        },
        strictPort: true,
        hmr: {
            host: 'axvicode.test',
            protocol: 'wss',
        },
    },
});
