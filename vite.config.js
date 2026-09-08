import { defineConfig } from 'vite';
import symfonyPlugin from 'vite-plugin-symfony';

export default defineConfig({
    plugins: [
        symfonyPlugin(),
    ],
    build: {
        rollupOptions: {
            input: {
                app: './assets/scripts/app.ts',
                appmain: './assets/scripts/sub/appmain.ts',
                appresize: './assets/scripts/sub/appresize.ts',
                appconvert: './assets/scripts/sub/appconvert.ts',
                appcompress: './assets/scripts/sub/appcompress.ts',
                approtate: './assets/scripts/sub/approtate.ts',
                appsepia: './assets/scripts/sub/appsepia.ts',
            },
        },
    },
});
