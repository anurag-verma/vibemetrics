<script setup>
import FlashToast from '@/Components/FlashToast.vue';
import Logo from '@/Components/Logo.vue';
import { useCurrentYear } from '@/Composables/useCurrentYear';
import { Link } from '@inertiajs/vue3';

const currentYear = useCurrentYear();
</script>

<template>
    <div class="guest-layout flex min-h-screen flex-col items-center bg-gradient-to-br from-slate-50 via-white to-indigo-50/50 px-4 pt-8 sm:justify-center sm:pt-0">
        <FlashToast />
        <div class="mb-8">
            <Logo size="lg" href="/" />
        </div>

        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200/80 bg-white/80 p-6 shadow-xl shadow-slate-200/50 backdrop-blur-sm sm:p-8">
            <slot />
        </div>

        <p class="mt-8 text-center text-xs text-slate-400">
            &copy; {{ currentYear }} VibeMetrics
            ·
            <Link :href="route('privacy')" class="hover:text-indigo-600">Privacy</Link>
            ·
            <Link :href="route('terms')" class="hover:text-indigo-600">Terms</Link>
        </p>
    </div>
</template>
