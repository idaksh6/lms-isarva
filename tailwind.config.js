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
                    50: 'rgb(var(--brand-50) / <alpha-value>)',
                    100: 'rgb(var(--brand-100) / <alpha-value>)',
                    200: 'rgb(var(--brand-200) / <alpha-value>)',
                    300: 'rgb(var(--brand-300) / <alpha-value>)',
                    400: 'rgb(var(--brand-400) / <alpha-value>)',
                    500: 'rgb(var(--brand-500) / <alpha-value>)',
                    600: 'rgb(var(--brand-600) / <alpha-value>)',
                    700: 'rgb(var(--brand-700) / <alpha-value>)',
                    800: 'rgb(var(--brand-800) / <alpha-value>)',
                    900: 'rgb(var(--brand-900) / <alpha-value>)',
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
