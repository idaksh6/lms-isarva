import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    safelist: [
        'border-sky-300',
        'bg-sky-100',
        'border-rose-300',
        'bg-rose-100',
        'border-emerald-300',
        'bg-emerald-100',
        'border-orange-300',
        'bg-orange-100',
        'bg-violet-100',
        'text-violet-700',
        'text-violet-800',
        'ring-violet-200',
        'text-sky-700',
        'text-sky-800',
        'ring-sky-200',
        'text-emerald-700',
        'text-emerald-800',
        'ring-emerald-200',
        'from-violet-100',
        'via-indigo-50',
        'from-sky-100',
        'via-blue-50',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#ecfdf8',
                    100: '#d1faf0',
                    200: '#a7f3e0',
                    300: '#6ee7c7',
                    400: '#34d399',
                    500: '#00A86B',
                    600: '#009966',
                    700: '#007a52',
                    800: '#006343',
                    900: '#004d35',
                },
                accent: {
                    50: '#fff7ed',
                    100: '#ffedd5',
                    200: '#fed7aa',
                    400: '#ff8c42',
                    500: '#F37021',
                    600: '#e5651a',
                    700: '#cc5a16',
                },
                isarva: {
                    dark: '#1e293b',
                    heading: '#0f172a',
                    muted: '#64748b',
                    border: '#e2e8f0',
                    surface: '#f8fafc',
                },
            },
            boxShadow: {
                isarva: '0 4px 24px rgba(0, 0, 0, 0.06)',
                'isarva-lg': '0 12px 40px rgba(0, 153, 102, 0.12)',
                card: '0 2px 16px rgba(15, 23, 42, 0.06)',
                'card-hover': '0 8px 28px rgba(15, 23, 42, 0.1)',
            },
        },
    },

    plugins: [forms],
};
