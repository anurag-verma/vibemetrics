import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                serif: ['Georgia', 'Cambria', 'Times New Roman', 'Times', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                brand: {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                },
                paper: {
                    DEFAULT: '#faf8f5',
                    dark: '#f3f0eb',
                },
                warm: {
                    50: '#faf8f5',
                    100: '#f3f0eb',
                    200: '#e7e2db',
                    800: '#44403c',
                },
            },
            boxShadow: {
                marketing: '0 25px 50px -12px rgba(15, 23, 42, 0.08)',
                'marketing-lg': '0 32px 64px -16px rgba(79, 70, 229, 0.18), 0 12px 24px -8px rgba(15, 23, 42, 0.08)',
                'marketing-glow': '0 0 40px -8px rgba(99, 102, 241, 0.45)',
                'card-hover': '0 20px 40px -12px rgba(99, 102, 241, 0.15), 0 8px 16px -8px rgba(15, 23, 42, 0.06)',
            },
            animation: {
                'fade-in': 'fadeIn 0.4s ease-out',
                'fade-in-up': 'fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                float: 'float 6s ease-in-out infinite',
                'float-slow': 'float 8s ease-in-out infinite',
                'pulse-glow': 'pulseGlow 4s ease-in-out infinite',
                shimmer: 'shimmer 3s ease-in-out infinite',
                'draw-line': 'drawLine 2s ease-out forwards',
                marquee: 'marquee 40s linear infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                fadeInUp: {
                    '0%': { opacity: '0', transform: 'translateY(24px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-12px)' },
                },
                pulseGlow: {
                    '0%, 100%': { opacity: '0.4', transform: 'scale(1)' },
                    '50%': { opacity: '0.7', transform: 'scale(1.05)' },
                },
                shimmer: {
                    '0%, 100%': { backgroundPosition: '0% 50%' },
                    '50%': { backgroundPosition: '100% 50%' },
                },
                drawLine: {
                    '0%': { strokeDashoffset: '1000' },
                    '100%': { strokeDashoffset: '0' },
                },
                marquee: {
                    '0%': { transform: 'translateX(0)' },
                    '100%': { transform: 'translateX(-50%)' },
                },
            },
        },
    },

    plugins: [forms],
};
