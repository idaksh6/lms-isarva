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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#2563eb',
                    600: '#1d4ed8',
                    700: '#1e40af',
                    800: '#1e3a8a',
                    900: '#172554',
                },
                accent: {
                    50: '#f8fafc',
                    100: '#f1f5f9',
                    200: '#e2e8f0',
                    400: '#64748b',
                    500: '#475569',
                    600: '#334155',
                    700: '#1e293b',
                },
                isarva: {
                    dark: '#1e293b',
                    heading: '#0f172a',
                    muted: '#64748b',
                    border: '#e2e8f0',
                    surface: '#f1f5f9',
                },
            },
            boxShadow: {
                isarva: '0 1px 3px rgba(15, 23, 42, 0.06), 0 1px 2px rgba(15, 23, 42, 0.04)',
                'isarva-lg': '0 4px 16px rgba(15, 23, 42, 0.08)',
                card: '0 1px 3px rgba(15, 23, 42, 0.06)',
                'card-hover': '0 4px 12px rgba(15, 23, 42, 0.08)',
            },
        },
    },

    plugins: [forms],
};
