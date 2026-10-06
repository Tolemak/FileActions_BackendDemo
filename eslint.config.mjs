import js from '@eslint/js';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    { ignores: ['public/build', 'var', 'vendor', 'node_modules'] },
    {
        extends: [js.configs.recommended, ...tseslint.configs.recommended],
        files: ['assets/**/*.{ts,js}'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: globals.browser,
        },
        rules: {
            '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
        },
    },
    {
        files: ['vite.config.js', 'vitest.config.js', 'eslint.config.mjs'],
        extends: [js.configs.recommended],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: globals.node,
        },
    },
);
