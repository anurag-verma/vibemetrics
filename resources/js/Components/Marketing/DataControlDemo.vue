<script setup>
const csvExports = [
    { label: 'Page views & paths', rows: '24,831', period: 'Last 30 days' },
    { label: 'Referrers & sources', rows: '8,412', period: 'Last 30 days' },
    { label: 'UTM campaigns', rows: '3,204', period: 'Last 30 days' },
];

const retentionOptions = [
    { label: '30 days', active: false },
    { label: '90 days', active: true },
    { label: '365 days', active: false },
];

const rollupDays = [
    { date: '07-06-2026', views: '847', visitors: '312' },
    { date: '06-06-2026', views: '921', visitors: '338' },
    { date: '05-06-2026', views: '804', visitors: '291' },
    { date: '04-06-2026', views: '876', visitors: '305' },
];
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
                            <span class="rounded-full bg-warm-100 px-2 py-0.5 text-[10px] font-medium text-warm-800">Data control</span>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">Export, retention & rollups</p>
                    </div>
                    <div class="rounded-2xl border border-indigo-200 bg-indigo-50/80 px-3 py-2 shadow-sm">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-indigo-500">Your data</p>
                        <p class="text-xs font-bold text-indigo-700">Full export access</p>
                    </div>
                </div>

                <div class="mt-3 rounded-xl border border-warm-200 bg-white p-3">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs font-semibold text-warm-800">CSV export</p>
                        <span class="text-[10px] text-slate-400">30d range</span>
                    </div>
                    <ul class="mt-2.5 space-y-1.5">
                        <li
                            v-for="item in csvExports"
                            :key="item.label"
                            class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-warm-100 bg-paper/60 px-2.5 py-2 text-xs"
                        >
                            <div class="min-w-0">
                                <p class="font-medium text-warm-800">{{ item.label }}</p>
                                <p class="text-[10px] text-slate-400">{{ item.rows }} rows · {{ item.period }}</p>
                            </div>
                            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-indigo-600 px-2.5 py-1 text-[10px] font-semibold text-white">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-warm-200 bg-white p-3">
                        <p class="text-xs font-semibold text-warm-800">Data retention</p>
                        <p class="mt-0.5 text-[10px] text-slate-500">Raw events auto-purged after period</p>
                        <div class="mt-2.5 flex flex-wrap gap-1.5">
                            <span
                                v-for="option in retentionOptions"
                                :key="option.label"
                                class="rounded-full px-2.5 py-1 text-[10px] font-medium transition"
                                :class="option.active
                                    ? 'bg-indigo-600 text-white shadow-sm'
                                    : 'border border-warm-200 bg-paper text-warm-800'"
                            >
                                {{ option.label }}
                            </span>
                        </div>
                        <p class="mt-2 text-[10px] text-emerald-600">90 days active · next purge Aug 6</p>
                    </div>

                    <div class="rounded-xl border border-warm-200 bg-white p-3">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-semibold text-warm-800">Nightly rollups</p>
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                                Enabled
                            </span>
                        </div>
                        <p class="mt-0.5 text-[10px] text-slate-500">Last run today at 2:00 AM</p>
                        <ul class="mt-2 space-y-1">
                            <li
                                v-for="day in rollupDays"
                                :key="day.date"
                                class="flex items-center justify-between rounded-lg bg-paper/60 px-2 py-1 text-[10px]"
                            >
                                <span class="font-medium text-slate-500">{{ day.date }}</span>
                                <span class="text-warm-800">
                                    <span class="font-semibold">{{ day.views }}</span>
                                    <span class="text-slate-400"> views · </span>
                                    <span class="font-semibold">{{ day.visitors }}</span>
                                    <span class="text-slate-400"> visitors</span>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
