<script setup>
import RankList from '@/Components/RankList.vue';
import { ref } from 'vue';

const eventTypes = {
    pageview: { label: 'Page view', class: 'bg-indigo-100 text-indigo-700' },
    spa: { label: 'SPA route', class: 'bg-violet-100 text-violet-700' },
    referrer: { label: 'Referrer', class: 'bg-sky-100 text-sky-700' },
    utm: { label: 'UTM campaign', class: 'bg-emerald-100 text-emerald-700' },
};

const events = [
    { time: '14:32', type: 'pageview', detail: '/pricing' },
    { time: '14:31', type: 'spa', detail: '/app/dashboard' },
    { time: '14:31', type: 'pageview', detail: '/docs/getting-started' },
    { time: '14:30', type: 'referrer', detail: 'google.com' },
    { time: '14:28', type: 'utm', detail: 'launch · twitter' },
    { time: '14:27', type: 'pageview', detail: '/' },
    { time: '14:26', type: 'spa', detail: '/app/settings/billing' },
    { time: '14:24', type: 'referrer', detail: 'news.ycombinator.com' },
];

const sourceTabs = [
    {
        id: 'referrers',
        label: 'Referrers',
        labelType: 'referrer',
        items: [
            { label: 'google.com', count: 5621 },
            { label: 'twitter.com', count: 1893 },
            { label: '(direct)', count: 1247 },
            { label: 'news.ycombinator.com', count: 892 },
            { label: 'github.com', count: 634 },
        ],
    },
    {
        id: 'campaigns',
        label: 'Campaigns',
        items: [
            { label: 'launch', count: 2104 },
            { label: 'newsletter-feb', count: 876 },
            { label: 'product-hunt', count: 543 },
            { label: 'blog-cta', count: 312 },
        ],
    },
    {
        id: 'utm_source',
        label: 'UTM source',
        items: [
            { label: 'twitter', count: 1842 },
            { label: 'google', count: 1203 },
            { label: 'newsletter', count: 876 },
            { label: 'linkedin', count: 421 },
        ],
    },
];

const activeTab = ref('referrers');

const activeSourceTab = () => sourceTabs.find((tab) => tab.id === activeTab.value) ?? sourceTabs[0];
</script>

<template>
    <div class="group relative animate-fade-in-up">
        <div
            class="pointer-events-none absolute -inset-6 -z-10 opacity-40 transition duration-700 group-hover:opacity-60"
            aria-hidden="true"
        >
            <svg class="h-full w-full" viewBox="0 0 400 400" fill="none">
                <circle cx="200" cy="200" r="160" stroke="#e7e2db" stroke-width="0.5" />
                <circle cx="200" cy="200" r="120" stroke="#e7e2db" stroke-width="0.5" />
            </svg>
        </div>

        <div class="vm-craft-card rounded-2xl p-1.5 transition duration-500 group-hover:-translate-y-1 group-hover:shadow-md">
            <div class="flex items-center gap-2 border-b border-warm-200 px-4 py-2.5">
                <div class="flex gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-rose-400" />
                    <span class="h-2.5 w-2.5 rounded-full bg-amber-400" />
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400" />
                </div>
                <span class="ml-1 truncate text-xs text-slate-400">app.vibemetrics.com/sites/demo</span>
            </div>

            <div class="p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-semibold text-warm-800">Demo Site</h3>
                            <span class="vm-badge-live text-[10px]">14 live</span>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">vibemetrics.com · Last 30 days</p>
                    </div>
                    <div class="flex flex-col items-end gap-2 sm:flex-row sm:items-center">
                        <div class="rounded-2xl border border-warm-200 bg-paper px-3 py-2 shadow-sm">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-slate-400">Filtered</p>
                            <p class="text-xs font-bold text-warm-800">847 bots blocked today</p>
                        </div>
                        <span class="rounded-lg border border-warm-200 bg-white px-2.5 py-1 text-[10px] font-medium text-slate-500">30d</span>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-3 gap-2">
                    <div class="rounded-xl border border-warm-200 bg-white p-2.5">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-slate-400">Views</p>
                        <p class="mt-0.5 text-base font-bold text-warm-800">24,831</p>
                        <p class="text-[10px] font-medium text-emerald-600">+12.4%</p>
                    </div>
                    <div class="rounded-xl border border-warm-200 bg-white p-2.5">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-slate-400">Events</p>
                        <p class="mt-0.5 text-base font-bold text-warm-800">31,204</p>
                        <p class="text-[10px] font-medium text-emerald-600">+9.8%</p>
                    </div>
                    <div class="rounded-xl border border-warm-200 bg-white p-2.5">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-slate-400">Today</p>
                        <p class="mt-0.5 text-base font-bold text-warm-800">847</p>
                        <p class="text-[10px] font-medium text-emerald-600">+18 vs yesterday</p>
                    </div>
                </div>

                <div class="mt-3 rounded-xl border border-warm-200 bg-white p-3">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs font-semibold text-warm-800">Recent events</p>
                        <div class="flex flex-wrap justify-end gap-1">
                            <span
                                v-for="(type, key) in eventTypes"
                                :key="key"
                                :class="type.class"
                                class="vm-pill-tag text-[10px]"
                            >
                                {{ type.label }}
                            </span>
                        </div>
                    </div>

                    <ul class="mt-2.5 space-y-1">
                        <li
                            v-for="(row, index) in events"
                            :key="index"
                            class="grid grid-cols-[2.5rem_5.5rem_1fr] items-center gap-2 rounded-lg border border-warm-100 bg-paper/60 px-2.5 py-1.5 text-xs transition hover:border-indigo-100 hover:bg-white"
                        >
                            <span class="font-mono text-[10px] text-slate-400">{{ row.time }}</span>
                            <span
                                :class="eventTypes[row.type].class"
                                class="inline-flex w-fit rounded-full px-1.5 py-0.5 text-[10px] font-medium"
                            >
                                {{ eventTypes[row.type].label }}
                            </span>
                            <span class="truncate font-medium text-slate-600">{{ row.detail }}</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-3 rounded-xl border border-warm-200 bg-white p-3">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-xs font-semibold text-warm-800">Sources</p>
                        <div class="flex max-w-full gap-0.5 overflow-x-auto rounded-lg bg-warm-100/80 p-0.5">
                            <button
                                v-for="tab in sourceTabs"
                                :key="tab.id"
                                type="button"
                                class="shrink-0 rounded-md px-2 py-1 text-[10px] font-medium transition"
                                :class="activeTab === tab.id
                                    ? 'bg-white text-warm-800 shadow-sm'
                                    : 'text-slate-500 hover:text-warm-800'"
                                @click="activeTab = tab.id"
                            >
                                {{ tab.label }}
                            </button>
                        </div>
                    </div>

                    <RankList
                        :items="activeSourceTab().items"
                        :label-type="activeSourceTab().labelType ?? 'auto'"
                        bare
                    />
                </div>
            </div>
        </div>
    </div>
</template>
