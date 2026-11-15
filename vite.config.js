import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Admin rich-text editor (Quill), loaded only on pages that edit rich text.
                'resources/css/admin-editor.css',
                'resources/js/admin-editor.js',
            ],
            refresh: true,
        }),
    ],
});
