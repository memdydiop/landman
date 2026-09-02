import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";
import os from 'node:os';

function resolveHmrHost() {
    // Permet de forcer via env: VITE_HMR_HOST=192.168.x.x
    if (process.env.VITE_HMR_HOST) {
        return process.env.VITE_HMR_HOST;
    }

    // Détection auto de l'IP LAN (priorise 192.168.x.x / 10.x.x.x)
    const nets = os.networkInterfaces();
    const candidates = [];

    for (const addrs of Object.values(nets)) {
        for (const net of addrs ?? []) {
            if (net.family === 'IPv4' && !net.internal) {
                candidates.push(net.address);
            }
        }
    }

    if (candidates.length === 0) return undefined; // fallback: Vite utilisera window.location.hostname

    // Priorise le réseau local physique plutôt que l'IP WSL/docker 172.x
    const preferred = candidates.find((ip) => ip.startsWith('192.168.') || ip.startsWith('10.'));
    return preferred ?? candidates[0];
}

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/passkeys.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: resolveHmrHost(),
            clientPort: 5173,
        },
        strictPort: true,
        cors: true,
        watch: {
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/storage/framework/views/**',
                '**/vendor/**',
            ],
        },
    },
});
