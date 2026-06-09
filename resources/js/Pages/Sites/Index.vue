<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { formatDisplayDate } from '@/utils/date';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    sites: Array,
    siteLimit: { type: Number, default: null },
    isUnlimitedSites: { type: Boolean, default: false },
    sitesUsed: Number,
    canAddSite: Boolean,
});

const usagePercent = computed(() => {
    if (props.isUnlimitedSites || !props.siteLimit) {
        return 0;
    }

    return props.siteLimit > 0 ? Math.min(100, (props.sitesUsed / props.siteLimit) * 100) : 0;
});

const formatCreated = (dateStr) => {
    if (!dateStr) return '—';
    return formatDisplayDate(dateStr);
};
</script>

<template>
    <Head title="Websites" />

    <AppLayout>
        <div class="mx-auto max-w-4xl space-y-6">
            <PageHeader title="Websites" description="Manage your tracked properties and usage limits.">
                <template v-if="canAddSite" #actions>
                    <Link :href="route('sites.create')" class="vm-btn-primary w-full sm:w-auto">
                        Add website
                    </Link>
                </template>
            </PageHeader>
            <!-- Usage meter -->
            <div class="vm-card">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600 dark:text-slate-400">
                        <template v-if="isUnlimitedSites">
                            {{ sitesUsed }} {{ sitesUsed === 1 ? 'site' : 'sites' }}
                        </template>
                        <template v-else>
                            {{ sitesUsed }} of {{ siteLimit }} sites used
                        </template>
                    </span>
                    <span v-if="!isUnlimitedSites && !canAddSite" class="text-slate-500 dark:text-slate-400">
                        Plan limit reached
                    </span>
                </div>
                <div
                    v-if="!isUnlimitedSites"
                    class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                >
                    <div
                        class="h-full rounded-full transition-all"
                        :class="usagePercent >= 100 ? 'bg-amber-500' : 'bg-indigo-500'"
                        :style="{ width: `${usagePercent}%` }"
                    />
                </div>
                <p v-if="!isUnlimitedSites && !canAddSite && sites.length" class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                    Contact an admin to increase the platform site limit.
                </p>
            </div>

            <!-- Site list -->
            <div v-if="sites.length" class="vm-card overflow-hidden p-0">
                <div class="hidden border-b border-slate-200 px-5 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:text-slate-400 sm:grid sm:grid-cols-[1fr_110px_80px_48px] sm:gap-4">
                    <span>Website</span>
                    <span class="text-right">Created</span>
                    <span class="text-center">Status</span>
                    <span class="sr-only">Actions</span>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    <div
                        v-for="site in sites"
                        :key="site.id"
                        class="flex flex-col gap-3 px-5 py-4 sm:grid sm:grid-cols-[1fr_110px_80px_48px] sm:items-center sm:gap-4"
                        :class="site.is_paused ? 'bg-slate-50/50 dark:bg-slate-800/30' : ''"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-400"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <Link
                                    :href="route('sites.show', site.id)"
                                    class="truncate font-semibold text-slate-900 underline decoration-slate-400 underline-offset-[3px] transition hover:text-indigo-600 hover:decoration-indigo-500 dark:text-white dark:decoration-slate-500 dark:hover:text-indigo-400 dark:hover:decoration-indigo-400"
                                >
                                    {{ site.name }}
                                </Link>
                                <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ site.domain }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end">
                            <span class="text-xs text-slate-500 sm:hidden">Created</span>
                            <span class="text-sm text-slate-600 dark:text-slate-400">
                                {{ formatCreated(site.created_at) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between sm:justify-center">
                            <span class="text-xs text-slate-500 sm:hidden">Status</span>
                            <span
                                class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="site.is_paused
                                    ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                    : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400'"
                            >
                                {{ site.is_paused ? 'Paused' : 'Active' }}
                            </span>
                        </div>

                        <div class="flex items-center sm:justify-end">
                            <Link
                                :href="route('sites.edit', site.id)"
                                class="inline-flex items-center justify-center rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                title="Edit website"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="vm-card py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-400">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">No websites yet</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Add a domain, copy the tracking snippet, and view your analytics overview.
                </p>
                <Link v-if="canAddSite" :href="route('sites.create')" class="vm-btn-primary mt-6 inline-flex">
                    Add your first website
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
