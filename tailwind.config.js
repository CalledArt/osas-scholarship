import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                cvsu: {
                    green: {
                        DEFAULT: '#0B6E4F',
                        dark: '#074A35',
                        light: '#0F9D63',
                    },
                    gold: {
                        DEFAULT: '#FFC72C',
                        dark: '#E6A800',
                    },
                },
            },
        },
    },

    plugins: [forms],
};
