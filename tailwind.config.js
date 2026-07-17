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
            colors: {
                brand: {
                    primary: '#15803D',
                    dark: '#0B5D2A',
                    light: '#F2FBF5',
                    orange: '#C2410C',
                    text: '#17221B',
                    muted: '#5F6F65',
                    border: '#D9EADF',
                    surface: '#FFFFFF',
                },
            },
            fontFamily: {
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                soft: '0 20px 50px rgba(11, 93, 42, 0.12)',
                card: '0 10px 30px rgba(23, 34, 27, 0.08)',
                lift: '0 16px 36px rgba(23, 34, 27, 0.13)',
            },
            keyframes: {
                'fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(14px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
            animation: {
                'fade-up': 'fade-up .45s ease-out both',
            },
        },
    },

    plugins: [forms],
};
