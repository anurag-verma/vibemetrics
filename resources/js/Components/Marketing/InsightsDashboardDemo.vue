<script setup>
import RankList from '@/Components/RankList.vue';
import { ref } from 'vue';

const metrics = [
    { label: 'Views', value: '24,831', change: '+12.4% vs prior 30d', accent: 'text-indigo-600' },
    { label: 'Visitors', value: '8,412', change: '+8.1% · privacy-safe estimate', accent: 'text-emerald-600' },
    { label: 'Views today', value: '847', change: '+18 vs yesterday', accent: 'text-amber-600' },
    { label: 'Pages / visitor', value: '2.95', change: '829 avg views/day', accent: 'text-rose-600' },
    { label: 'Live now', value: '14', change: 'Active in last 5 min', accent: 'text-indigo-600' },
];

const pageTabs = [
    {
        id: 'paths',
        label: 'Path',
        labelType: 'path',
        items: [
            { label: '/', count: 9214 },
            { label: '/pricing', count: 4102 },
            { label: '/docs/getting-started', count: 2847 },
            { label: '/blog/privacy-analytics', count: 1923 },
            { label: '/features', count: 874 },
        ],
    },
    {
        id: 'urls',
        label: 'URL',
        items: [
            { label: 'https://vibemetrics.com/', count: 9214 },
            { label: 'https://vibemetrics.com/pricing', count: 4102 },
            { label: 'https://vibemetrics.com/docs', count: 2847 },
        ],
    },
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
        ],
    },
    {
        id: 'channels',
        label: 'Channels',
        items: [
            { label: 'Organic search', count: 6120 },
            { label: 'Social', count: 2840 },
            { label: 'Direct', count: 1247 },
            { label: 'Referral', count: 1624 },
        ],
    },
];

const activePageTab = ref('paths');
const activeSourceTab = ref('referrers');

const currentPageTab = () => pageTabs.find((tab) => tab.id === activePageTab.value) ?? pageTabs[0];
const currentSourceTab = () => sourceTabs.find((tab) => tab.id === activeSourceTab.value) ?? sourceTabs[0];

const goals = [
    { name: 'Blog reader', pattern: '/blog/', completions: 346, rate: '100%' },
    { name: 'Pricing page visit', pattern: '/pricing', completions: 166, rate: '100%' },
];

const trafficBars = [
    { views: 22, visitors: 6 }, { views: 30, visitors: 8 }, { views: 18, visitors: 5 }, { views: 34, visitors: 9 },
    { views: 24, visitors: 6 }, { views: 58, visitors: 15 }, { views: 62, visitors: 16 }, { views: 26, visitors: 7 },
    { views: 32, visitors: 8 }, { views: 38, visitors: 9 }, { views: 42, visitors: 10 }, { views: 34, visitors: 8 },
    { views: 40, visitors: 9 }, { views: 46, visitors: 11 }, { views: 40, visitors: 10 }, { views: 64, visitors: 17 },
].map((day, index) => ({
    viewsX: index * 20 + 2,
    visitorsX: index * 20 + 10,
    viewsHeight: day.views * 1.3,
    visitorsHeight: day.visitors * 1.3,
}));
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
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-800">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                                Receiving data
                            </span>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">vibemetrics.com · Last 30 days</p>
                    </div>
                    <div class="flex flex-col items-end gap-2 sm:flex-row sm:items-center">
                        <div class="rounded-2xl border border-warm-200 bg-indigo-50/80 px-3 py-2 shadow-sm">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-indigo-500">Trending up</p>
                            <p class="text-xs font-bold text-indigo-700">+12.4% vs last period</p>
                        </div>
                        <span class="rounded-lg border border-warm-200 bg-white px-2.5 py-1 text-[10px] font-medium text-slate-500">30d</span>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
                    <div
                        v-for="metric in metrics"
                        :key="metric.label"
                        class="rounded-xl border border-warm-200 bg-white p-2.5"
                    >
                        <p class="text-[10px] font-medium uppercase tracking-wider text-slate-400">{{ metric.label }}</p>
                        <p class="mt-0.5 text-sm font-bold text-warm-800 sm:text-base">{{ metric.value }}</p>
                        <p class="text-[10px] font-medium" :class="metric.accent">{{ metric.change }}</p>
                    </div>
                </div>

                <div class="mt-3 rounded-xl border border-warm-200 bg-white p-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-xs font-semibold text-warm-800">Traffic (30d)</p>
                        <div class="flex items-center gap-3 text-[10px] font-medium">
                            <span class="flex items-center gap-1 text-indigo-600">
                                <span class="h-2 w-2 rounded-full bg-indigo-500" />
                                Views
                            </span>
                            <span class="flex items-center gap-1 text-emerald-600">
                                <span class="h-2 w-2 rounded-full bg-emerald-500" />
                                Visitors
                            </span>
                        </div>
                    </div>
                    <svg class="mt-2 h-24 w-full sm:h-28" viewBox="0 0 320 100" preserveAspectRatio="none" aria-hidden="true">
                        <g v-for="(bar, index) in trafficBars" :key="index">
                            <rect
                                :x="bar.viewsX"
                                :y="100 - bar.viewsHeight"
                                width="7"
                                :height="bar.viewsHeight"
                                rx="1.5"
                                fill="#6366f1"
                            />
                            <rect
                                :x="bar.visitorsX"
                                :y="100 - bar.visitorsHeight"
                                width="5"
                                :height="bar.visitorsHeight"
                                rx="1.5"
                                fill="#10b981"
                            />
                        </g>
                    </svg>
                </div>

                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-warm-200 bg-white p-3">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs font-semibold text-warm-800">Pages</p>
                            <div class="flex gap-0.5 rounded-lg bg-warm-100/80 p-0.5">
                                <button
                                    v-for="tab in pageTabs"
                                    :key="tab.id"
                                    type="button"
                                    class="shrink-0 rounded-md px-2 py-1 text-[10px] font-medium transition"
                                    :class="activePageTab === tab.id
                                        ? 'bg-white text-warm-800 shadow-sm'
                                        : 'text-slate-500 hover:text-warm-800'"
                                    @click="activePageTab = tab.id"
                                >
                                    {{ tab.label }}
                                </button>
                            </div>
                        </div>
                        <RankList
                            :items="currentPageTab().items"
                            :label-type="currentPageTab().labelType ?? 'auto'"
                            bare
                        />
                    </div>

                    <div class="rounded-xl border border-warm-200 bg-white p-3">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs font-semibold text-warm-800">Sources</p>
                            <div class="flex gap-0.5 rounded-lg bg-warm-100/80 p-0.5">
                                <button
                                    v-for="tab in sourceTabs"
                                    :key="tab.id"
                                    type="button"
                                    class="shrink-0 rounded-md px-2 py-1 text-[10px] font-medium transition"
                                    :class="activeSourceTab === tab.id
                                        ? 'bg-white text-warm-800 shadow-sm'
                                        : 'text-slate-500 hover:text-warm-800'"
                                    @click="activeSourceTab = tab.id"
                                >
                                    {{ tab.label }}
                                </button>
                            </div>
                        </div>
                        <RankList
                            :items="currentSourceTab().items"
                            :label-type="currentSourceTab().labelType ?? 'auto'"
                            bare
                        />
                    </div>
                </div>

                <div class="mt-3 rounded-xl border border-warm-200 bg-white p-3">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs font-semibold text-warm-800">Goals</p>
                        <span class="text-[10px] text-slate-400">Conversion tracking</span>
                    </div>
                    <ul class="mt-2.5 space-y-1.5">
                        <li
                            v-for="goal in goals"
                            :key="goal.name"
                            class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-warm-100 bg-paper/60 px-2.5 py-1.5 text-xs"
                        >
                            <div class="flex min-w-0 items-center gap-2">
                                <span class="truncate font-medium text-warm-800">{{ goal.name }}</span>
                                <span class="shrink-0 rounded bg-warm-100 px-1.5 py-0.5 font-mono text-[10px] text-slate-500">{{ goal.pattern }}</span>
                            </div>
                            <span class="shrink-0 text-[10px] font-medium text-emerald-600">{{ goal.completions }} · {{ goal.rate }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
