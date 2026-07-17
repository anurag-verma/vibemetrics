<script setup>
const complianceChecks = [
    'No cookies required',
    'No IP addresses stored',
    'No cross-site tracking',
    'GDPR-friendly by design',
    'Privacy-safe visitor estimates',
    'Session-only fingerprinting',
];

const collected = [
    { label: 'Page URL & referrer', status: 'Aggregated only' },
    { label: 'Browser, OS & device', status: 'Derived from UA' },
    { label: 'Country', status: 'From CDN headers' },
    { label: 'UTM parameters', status: 'When present' },
    { label: 'Custom event names', status: 'Developer-defined' },
];

const neverStored = [
    'IP addresses',
    'Cookies or local storage',
    'Personally identifiable info',
    'Cross-site browsing history',
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
                <span class="ml-1 truncate text-xs text-slate-400">app.vibemetrics.com/privacy</span>
            </div>

            <div class="p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-semibold text-warm-800">Privacy overview</h3>
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-800">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                Cookieless
                            </span>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">Built for GDPR-friendly analytics</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 px-3 py-2 shadow-sm">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-emerald-600">Status</p>
                        <p class="text-xs font-bold text-emerald-800">No consent banner needed</p>
                    </div>
                </div>

                <div class="mt-3 rounded-xl border border-warm-200 bg-white p-3">
                    <p class="text-xs font-semibold text-warm-800">Compliance checklist</p>
                    <ul class="mt-2.5 grid gap-1.5 sm:grid-cols-2">
                        <li
                            v-for="check in complianceChecks"
                            :key="check"
                            class="flex items-center gap-2 rounded-lg border border-warm-100 bg-paper/60 px-2.5 py-2 text-xs"
                        >
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span class="font-medium text-warm-800">{{ check }}</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-warm-200 bg-white p-3">
                        <p class="text-xs font-semibold text-warm-800">What we collect</p>
                        <ul class="mt-2.5 space-y-1.5">
                            <li
                                v-for="item in collected"
                                :key="item.label"
                                class="flex items-center justify-between gap-2 rounded-lg border border-warm-100 bg-paper/60 px-2.5 py-1.5 text-xs"
                            >
                                <span class="font-medium text-slate-700">{{ item.label }}</span>
                                <span class="shrink-0 rounded-full bg-indigo-50 px-1.5 py-0.5 text-[10px] font-medium text-indigo-600">{{ item.status }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-xl border border-warm-200 bg-white p-3">
                        <p class="text-xs font-semibold text-warm-800">What we never store</p>
                        <ul class="mt-2.5 space-y-1.5">
                            <li
                                v-for="item in neverStored"
                                :key="item"
                                class="flex items-center gap-2 rounded-lg border border-rose-100 bg-rose-50/40 px-2.5 py-1.5 text-xs"
                            >
                                <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-500">
                                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </span>
                                <span class="font-medium text-slate-700">{{ item }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-3 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50/50 px-3 py-2.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-emerald-600 shadow-sm ring-1 ring-emerald-500/20">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-semibold text-warm-800">Visitors stay anonymous</p>
                        <p class="text-[10px] text-slate-500">No fingerprinting, no cross-site profiles — measure traffic, not people.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
