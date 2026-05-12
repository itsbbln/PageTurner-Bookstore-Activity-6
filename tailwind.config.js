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
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                matcha: {
                    50: '#E4EDB6',
                    100: '#D5E4AA',
                    200: '#C5D098',
                    300: '#B9DCA9',
                    400: '#D3E171',
                    500: '#BAC67A',
                    600: '#A4B05B',
                    700: '#7A872C',
                    800: '#52681D',
                    900: '#2E3D17',
                    950: '#035718',
                    accent: '#378552',
                    dark: '#51610F',
                    gold: '#DCD675',
                    lime: '#BDBE2E',
                },
            },
        },
    },

    plugins: [forms],
};
