<script setup>
import Logo from '@/Components/Logo.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    statuses: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div class="min-h-screen bg-paper px-4 py-12 text-warm-800">
        <Head title="Error page previews" />

        <div class="mx-auto max-w-lg">
            <Logo size="md" />

            <p class="mt-6 text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600">
                Local development only
            </p>

            <h1 class="mt-2 font-serif text-3xl font-bold tracking-tight text-warm-900">
                Error page previews
            </h1>

            <p class="mt-3 text-sm leading-relaxed text-slate-600">
                Open each status below to preview the branded error experience without triggering a real failure.
            </p>

            <ul class="mt-8 space-y-2">
                <li v-for="item in statuses" :key="item.code">
                    <Link
                        :href="route('preview-errors.show', item.code)"
                        class="flex items-center justify-between rounded-xl border border-warm-200/80 bg-white/80 px-4 py-3 text-sm transition hover:border-indigo-200 hover:shadow-sm"
                    >
                        <span class="font-medium text-warm-900">{{ item.label }}</span>
                        <span class="font-mono text-xs text-slate-500">{{ item.code }}</span>
                    </Link>
                </li>
            </ul>

            <Link
                :href="route('home')"
                class="mt-8 inline-block text-sm font-medium text-slate-600 underline-offset-4 hover:text-indigo-600 hover:underline"
            >
                Back to homepage
            </Link>
        </div>
    </div>
</template>
