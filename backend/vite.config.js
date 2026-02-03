import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'
import fs from 'fs'
import path from 'path'

function getJsEntries(dir) {
    return fs
        .readdirSync(dir)
        .filter(file => file.endsWith('.js'))
        .map(file => path.join(dir, file))
}

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                ...getJsEntries('resources/js'),
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'localhost',
        },
        watch: {
            usePolling: true,
            ignored: ['**/storage/framework/views/**'],
        },
    },
})
