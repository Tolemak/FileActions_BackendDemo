import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";

/* if you're using React */
// import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        /* react(), // if you're using React */
        symfonyPlugin(),
    ],
    build: {
        rollupOptions: {
            input: {
                app: "./assets/scripts/app.ts",
                appmain: "./assets/scripts/sub/appmain.ts",
                appresize: "./assets/scripts/sub/appresize.ts",
                appconvert: "./assets/scripts/sub/appconvert.ts",
                appcompress: './assets/scripts/sub/appcompress.ts', // Add this entry for the Compress feature

            },
        }
    },
    server: {
        proxy: {
            '/api': {
                target: 'http://localhost:8080/',
                changeOrigin: true,
                secure: false,
                rewrite: (path) => path.replace(/^\/api/, '')
            },
            cors: false
        },
    },
});
