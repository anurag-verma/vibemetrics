<script setup>
import RankList from '@/Components/RankList.vue';

const metrics = [
    { label: 'Views', value: '24,831', change: '+12.4% vs prior 30d', accent: 'text-indigo-600' },
    { label: 'Visitors', value: '8,412', change: '+8.1%', accent: 'text-emerald-600' },
    { label: 'Views today', value: '847', change: '+18 vs yesterday', accent: 'text-amber-600' },
    { label: 'Pages / visitor', value: '2.95', change: '829 avg views/day', accent: 'text-rose-600' },
    { label: 'Live now', value: '14', change: 'Active in last 5 min', accent: 'text-indigo-600' },
];

const topPages = [
    { label: '/', count: 9214 },
    { label: '/pricing', count: 4102 },
    { label: '/docs/getting-started', count: 2847 },
];

const referrers = [
    { label: 'google.com', count: 5621 },
    { label: 'twitter.com', count: 1893 },
    { label: '(direct)', count: 1247 },
];
</script>

<template>
    <div class="group relative animate-fade-in-up">
        <div
            class="pointer-events-none absolute -inset-8 -z-10 opacity-50 transition duration-700 group-hover:opacity-80"
            aria-hidden="true"
        >
            <svg class="h-full w-full" viewBox="0 0 400 400" fill="none">
                <circle cx="200" cy="200" r="180" stroke="#e7e2db" stroke-width="0.5" class="transition duration-700 group-hover:stroke-indigo-200" />
                <circle cx="200" cy="200" r="140" stroke="#e7e2db" stroke-width="0.5" />
                <circle cx="200" cy="200" r="100" stroke="#e7e2db" stroke-width="0.5" />
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

            <div class="p-3 sm:p-3.5">
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
                        <div class="rounded-2xl border border-warm-200 bg-paper px-3 py-2 shadow-sm">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-slate-400">Live visitors</p>
                            <p class="text-xs font-bold text-warm-800">14 online now</p>
                        </div>
                        <div class="rounded-2xl border border-indigo-200 bg-indigo-50/80 px-3 py-2 shadow-sm">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-indigo-500">Today</p>
                            <p class="text-xs font-bold text-indigo-700">+847 views</p>
                        </div>
                    </div>
                </div>

                <div class="mt-2 grid grid-cols-2 gap-1.5 sm:grid-cols-3 lg:grid-cols-5">
                    <div
                        v-for="metric in metrics"
                        :key="metric.label"
                        class="rounded-xl border border-warm-200 bg-white p-2 transition duration-300 hover:border-indigo-100 hover:shadow-sm"
                    >
                        <p class="text-[10px] font-medium uppercase tracking-wider text-slate-400">{{ metric.label }}</p>
                        <p class="mt-0.5 text-sm font-bold text-warm-800 sm:text-base">{{ metric.value }}</p>
                        <p class="text-[10px] font-medium" :class="metric.accent">{{ metric.change }}</p>
                    </div>
                </div>

                <div class="mt-2 rounded-xl border border-warm-200 bg-white p-2.5">
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
                    <svg class="mt-1.5 h-20 w-full" viewBox="0 0 320 100" preserveAspectRatio="none" aria-hidden="true">
                        <defs>
                            <linearGradient id="heroViewsFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#6366f1" stop-opacity="0.12" />
                                <stop offset="100%" stop-color="#6366f1" stop-opacity="0" />
                            </linearGradient>
                            <linearGradient id="heroVisitorsFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.08" />
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <path
                            d="M0,80 L20,72 L40,65 L60,58 L80,62 L100,45 L120,50 L140,35 L160,40 L180,28 L200,32 L220,20 L240,25 L260,15 L280,18 L300,10 L320,8 L320,100 L0,100 Z"
                            fill="url(#heroViewsFill)"
                        />
                        <path
                            d="M0,88 L20,82 L40,78 L60,72 L80,74 L100,62 L120,66 L140,55 L160,58 L180,48 L200,52 L220,42 L240,46 L260,38 L280,40 L300,34 L320,32 L320,100 L0,100 Z"
                            fill="url(#heroVisitorsFill)"
                        />
                        <path
                            d="M0,80 L20,72 L40,65 L60,58 L80,62 L100,45 L120,50 L140,35 L160,40 L180,28 L200,32 L220,20 L240,25 L260,15 L280,18 L300,10 L320,8"
                            fill="none"
                            stroke="#6366f1"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="vm-chart-line"
                        />
                        <path
                            d="M0,88 L20,82 L40,78 L60,72 L80,74 L100,62 L120,66 L140,55 L160,58 L180,48 L200,52 L220,42 L240,46 L260,38 L280,40 L300,34 L320,32"
                            fill="none"
                            stroke="#10b981"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>

                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                    <div class="rounded-xl border border-warm-200 bg-white p-2.5">
                        <p class="mb-1.5 text-xs font-semibold text-warm-800">Pages</p>
                        <RankList :items="topPages" label-type="path" bare />
                    </div>
                    <div class="rounded-xl border border-warm-200 bg-white p-2.5">
                        <p class="mb-1.5 text-xs font-semibold text-warm-800">Sources</p>
                        <RankList :items="referrers" label-type="referrer" bare />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
