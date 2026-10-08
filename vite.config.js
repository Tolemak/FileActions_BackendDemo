import { defineConfig } from 'vite';
import symfonyPlugin from 'vite-plugin-symfony';

export default defineConfig({
    plugins: [
        symfonyPlugin(),
    ],
    resolve: {
        alias: [
            { find: /^sweetalert2$/, replacement: 'sweetalert2/dist/sweetalert2.esm.js' },
        ],
    },
    build: {
        rollupOptions: {
            input: {
                app: './assets/scripts/app.ts',
                appmain: './assets/scripts/sub/appmain.ts',
                appaction: './assets/scripts/sub/appaction.ts',
            },
        },
    },
});
