import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: [
                    'Inter var', 'Inter', 'system-ui', '-apple-system', 'Segoe UI',
                    'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif',
                ],
                mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace'],
            },
            colors: {
                brand: {
                    50: '#eefbf7',
                    100: '#d5f5eb',
                    200: '#aeead8',
                    300: '#79d9c1',
                    400: '#43c0a4',
                    500: '#1fa78a',
                    600: '#12866f',
                    700: '#116b5b',
                    800: '#12564a',
                    900: '#11473f',
                    950: '#042a26',
                },
                ink: {
                    50: '#f6f7f9',
                    100: '#eceef2',
                    200: '#d5dae3',
                    300: '#b0b9c9',
                    400: '#8493ab',
                    500: '#647591',
                    600: '#4f5d78',
                    700: '#414c62',
                    800: '#384153',
                    900: '#323947',
                    950: '#1a1f2a',
                },
            },
            boxShadow: {
                card: '0 1px 2px 0 rgb(16 24 40 / 0.04), 0 1px 3px 0 rgb(16 24 40 / 0.06)',
                pop: '0 12px 32px -8px rgb(16 24 40 / 0.18), 0 4px 12px -4px rgb(16 24 40 / 0.10)',
            },
            borderRadius: {
                xl: '0.75rem',
                '2xl': '1rem',
            },
            keyframes: {
                'slide-up': {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'fade-in': {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
            },
            animation: {
                'slide-up': 'slide-up .18s ease-out',
                'fade-in': 'fade-in .15s ease-out',
            },
        },
    },

    plugins: [forms],
};
