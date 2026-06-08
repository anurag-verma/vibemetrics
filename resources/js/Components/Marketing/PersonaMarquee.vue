<script setup>
const personas = [
    { name: 'Tom', role: 'Indie founder', uses: 'Landing page conversions, referrers, UTM campaigns', initial: 'T', color: 'bg-indigo-100 text-indigo-700' },
    { name: 'Ana', role: 'Content marketer', uses: 'Blog traffic, top pages, campaign ROI', initial: 'A', color: 'bg-rose-100 text-rose-700' },
    { name: 'Gagan', role: 'Product manager', uses: 'Feature usage pages, daily trends, exports', initial: 'G', color: 'bg-violet-100 text-violet-700' },
    { name: 'Aaron', role: 'Developer', uses: 'SPA routes, bot filtering, lightweight script', initial: 'A', color: 'bg-emerald-100 text-emerald-700' },
    { name: 'Seoyoung', role: 'Creator', uses: 'Audience growth, referrer sources, live visitors', initial: 'S', color: 'bg-amber-100 text-amber-700' },
    { name: 'Gian', role: 'Agency lead', uses: 'Multi-site dashboards, client reports, CSV exports', initial: 'G', color: 'bg-sky-100 text-sky-700' },
];

const doubled = [...personas, ...personas];
</script>

<template>
    <div class="vm-marquee py-4">
        <div class="vm-marquee-track px-4">
            <div
                v-for="(persona, i) in doubled"
                :key="`${persona.name}-${i}`"
                class="vm-persona-card"
            >
                <div class="flex items-center gap-3">
                    <span :class="persona.color" class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold">
                        {{ persona.initial }}
                    </span>
                    <div>
                        <p class="font-semibold text-warm-800">{{ persona.name }}</p>
                        <p class="text-xs text-slate-500">{{ persona.role }}</p>
                    </div>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ persona.uses }}</p>
            </div>
        </div>
    </div>
</template>
