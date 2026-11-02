import forms from '@tailwindcss/forms';
import containerQueries from '@tailwindcss/container-queries';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    darkMode: 'class',
    plugins: [forms, containerQueries],
    theme: {
        extend: {
            fontFamily: {
                sans: ['IBM Plex Sans Arabic', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
        },
    },
};
