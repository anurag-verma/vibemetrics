<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canAddSite: Boolean,
    siteLimit: { type: Number, default: null },
    isUnlimitedSites: { type: Boolean, default: false },
    sitesUsed: Number,
});

const steps = [
    {
        number: 1,
        title: 'Add your website',
        description: 'Enter a name and domain for the site you want to track.',
    },
    {
        number: 2,
        title: 'Install the tracking snippet',
        description: 'Copy one lightweight script into your site’s HTML.',
    },
    {
        number: 3,
        title: 'View your overview',
        description: 'See pageviews, referrers, and live visitors in your dashboard.',
    },
];
</script>

<template>
    <Head title="Getting Started" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Getting started</h1>
        </template>

        <div class="mx-auto max-w-2xl space-y-6">
            <div class="vm-card overflow-hidden">
                <div class="border-b border-slate-100 bg-gradient-to-br from-emerald-50/80 to-indigo-50/50 px-6 py-8 text-center dark:border-slate-800 dark:from-emerald-950/30 dark:to-indigo-950/20">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="mt-4 text-2xl font-bold text-slate-900 dark:text-white">
                        You’re ready to track traffic
                    </h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Add your first website to start collecting privacy-friendly analytics.
                    </p>
                </div>

                <div class="space-y-4 px-6 py-6">
                    <div
                        v-for="step in steps"
                        :key="step.number"
                        class="flex gap-4"
                    >
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                            {{ step.number }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-white">{{ step.title }}</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ step.description }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        <template v-if="isUnlimitedSites">
                            {{ sitesUsed }} {{ sitesUsed === 1 ? 'site' : 'sites' }} on your account
                        </template>
                        <template v-else>
                            {{ sitesUsed }} of {{ siteLimit }} sites available on your plan
                        </template>
                    </p>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <Link :href="route('documentation')" class="vm-btn-secondary text-center">
                            View documentation
                        </Link>
                        <Link
                            v-if="canAddSite"
                            :href="route('sites.create')"
                            class="vm-btn-primary text-center"
                        >
                            Add your first website
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
