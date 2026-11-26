import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
const kebabCase = name => name
    .replace(/([A-Z]+)([A-Z][a-z])/g, '$1-$2')
    .replace(/([a-z0-9])([A-Z])/g, '$1-$2')
    .toLowerCase();

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/scss/app.scss',
                'resources/scss/public.scss',
                'resources/scss/playground.scss',
                'resources/js/app.tsx',
                'resources/js/playground/index.tsx',
                'resources/js/playground/health.ts',
                'resources/js/theme-switcher.tsx',
            ],
            refresh: true,
        }),
        react(),
    ],
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: 'manifest.json',
        minify: 'oxc',
        rolldownOptions: {
            output: {
                entryFileNames: chunk => `js/${kebabCase(chunk.name)}.js`,
                chunkFileNames: chunk => `js/${kebabCase(chunk.name)}-[hash].js`,
                assetFileNames: asset => `assets/${kebabCase(asset.name ?? 'asset')}`,
            },
        },
    },
    server: {
        watch: {
            ignored: [
                '**/storage/framework/views/**',
            ],
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                silenceDeprecations: [
                    'import',
                    'global-builtin',
                    'color-functions',
                    'if-function',
                ],
            },
        },
    },
});
