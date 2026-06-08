<script setup>
import GradientMesh from '@/Components/Marketing/GradientMesh.vue';
import Logo from '@/Components/Logo.vue';
import { useCurrentYear } from '@/Composables/useCurrentYear';
import { Link } from '@inertiajs/vue3';

defineProps({
    status: {
        type: [Number, String],
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        required: true,
    },
});

const currentYear = useCurrentYear();
</script>

<template>
    <div class="relative flex min-h-screen flex-col overflow-hidden bg-paper text-warm-800">
        <div class="vm-section-mesh absolute inset-0 -z-20" aria-hidden="true" />
        <GradientMesh variant="hero" />

        <div
            class="pointer-events-none absolute inset-0 -z-10 flex items-center justify-center overflow-hidden"
            aria-hidden="true"
        >
            <span
                class="select-none bg-gradient-to-b from-warm-200/80 via-indigo-100/40 to-transparent bg-clip-text font-serif text-[clamp(12rem,32vw,22rem)] font-bold leading-none tracking-tighter text-transparent"
            >
                {{ status }}
            </span>
        </div>

        <header class="relative z-10 mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-6 sm:px-6 lg:px-8">
            <Logo size="nav" />
            <span class="rounded-full border border-warm-200/80 bg-paper/60 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 backdrop-blur-sm">
                Error {{ status }}
            </span>
        </header>

        <main class="relative z-10 flex flex-1 flex-col justify-center px-4 pb-16 pt-4 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-3xl">
                <div class="mb-6 flex items-center gap-3">
                    <span class="h-px w-10 bg-gradient-to-r from-indigo-500 to-violet-500" />
                    <span class="text-xs font-semibold uppercase tracking-[0.25em] text-indigo-600">
                        {{ status }}
                    </span>
                </div>

                <h1 class="font-serif text-4xl font-bold tracking-tight text-warm-900 sm:text-5xl lg:text-6xl">
                    {{ title }}
                </h1>

                <p class="mt-5 max-w-xl text-lg leading-relaxed text-slate-600 sm:text-xl">
                    {{ message }}
                </p>

                <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <Link :href="route('home')" class="vm-btn-primary px-7 py-3 text-sm">
                        Go to homepage
                    </Link>
                    <button
                        type="button"
                        class="vm-btn-secondary px-7 py-3 text-sm"
                        @click="history.back()"
                    >
                        Go back
                    </button>
                </div>

                <div class="mt-16 grid gap-4 border-t border-warm-200/80 pt-8 sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Need help?</p>
                        <Link
                            :href="route('docs')"
                            class="mt-1 inline-block text-sm font-medium text-warm-800 underline-offset-4 transition hover:text-indigo-600 hover:underline"
                        >
                            Browse documentation
                        </Link>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Product</p>
                        <Link
                            :href="route('features')"
                            class="mt-1 inline-block text-sm font-medium text-warm-800 underline-offset-4 transition hover:text-indigo-600 hover:underline"
                        >
                            Explore features
                        </Link>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status</p>
                        <p class="mt-1 text-sm font-medium text-slate-600">
                            HTTP {{ status }}
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <footer class="relative z-10 border-t border-warm-200/60 bg-paper/40 px-4 py-5 text-center text-xs text-slate-400 backdrop-blur-sm sm:px-6">
            &copy; {{ currentYear }} VibeMetrics
        </footer>
    </div>
</template>
