<script setup>
import { computed } from 'vue';

const props = defineProps({
    countries: { type: Array, default: () => [] },
});

const regionNames = typeof Intl !== 'undefined'
    ? new Intl.DisplayNames(['en'], { type: 'region' })
    : null;

const countryCoords = {
    US: [22, 38], CA: [18, 28], MX: [18, 48], BR: [32, 68], AR: [30, 82],
    GB: [47, 30], FR: [49, 36], DE: [52, 32], ES: [47, 42], IT: [53, 40],
    NL: [50, 31], SE: [54, 24], PL: [55, 34], RU: [68, 26], TR: [58, 42],
    IN: [72, 48], CN: [78, 40], JP: [86, 40], KR: [84, 38], AU: [88, 72],
    NZ: [94, 78], ZA: [55, 78], NG: [50, 56], EG: [56, 48], AE: [63, 46],
    SG: [79, 54], ID: [80, 58], TH: [77, 50], PH: [84, 52], MY: [78, 56],
    PK: [68, 44], BD: [74, 46], VN: [79, 50], CO: [26, 58], CL: [28, 76],
    PE: [26, 64], IE: [45, 30], PT: [45, 40], CH: [51, 35], AT: [53, 34],
    BE: [49, 33], NO: [51, 22], FI: [56, 22], DK: [51, 28], GR: [55, 42],
    IL: [58, 44], SA: [61, 48], HK: [81, 44], TW: [83, 44],
};

const maxCount = computed(() => Math.max(...props.countries.map((c) => c.count), 1));

const points = computed(() => props.countries
    .filter((country) => countryCoords[country.label?.toUpperCase()])
    .map((country) => {
        const code = country.label.toUpperCase();
        const [x, y] = countryCoords[code];

        return {
            code,
            name: regionNames?.of(code) ?? code,
            count: country.count,
            x,
            y,
            size: 4 + (country.count / maxCount.value) * 16,
            opacity: 0.35 + (country.count / maxCount.value) * 0.65,
        };
    }));

const hasData = computed(() => props.countries.length > 0);
</script>

<template>
    <div class="vm-card h-full min-h-[280px] animate-fade-in">
        <h3 class="vm-panel-title mb-4">Location</h3>

        <div v-if="hasData" class="space-y-4">
            <div class="relative overflow-hidden rounded-xl bg-slate-900/95 p-4 dark:bg-slate-950">
                <svg viewBox="0 0 100 50" class="h-40 w-full" aria-hidden="true">
                    <ellipse cx="50" cy="25" rx="48" ry="22" fill="none" stroke="rgba(148,163,184,0.15)" stroke-width="0.3" />
                    <ellipse cx="50" cy="25" rx="36" ry="16" fill="none" stroke="rgba(148,163,184,0.08)" stroke-width="0.2" />
                    <circle
                        v-for="point in points"
                        :key="point.code"
                        :cx="point.x"
                        :cy="point.y"
                        :r="point.size / 4"
                        fill="#6366f1"
                        :opacity="point.opacity"
                    >
                        <title>{{ point.name }}: {{ point.count.toLocaleString() }}</title>
                    </circle>
                </svg>
            </div>

            <ul class="space-y-2">
                <li
                    v-for="(country, index) in countries.slice(0, 5)"
                    :key="country.label"
                    class="flex items-center justify-between gap-3 text-sm"
                >
                    <span class="truncate text-slate-700 dark:text-slate-300">
                        <span class="mr-2 text-xs text-slate-400">{{ index + 1 }}</span>
                        {{
                            country.label.length === 2 && country.label.toUpperCase() === 'XX'
                                ? 'Unknown'
                                : (country.label.length === 2 && regionNames
                                    ? regionNames.of(country.label.toUpperCase()) ?? country.label
                                    : country.label)
                        }}
                    </span>
                    <span class="shrink-0 font-semibold text-slate-600 dark:text-slate-400">
                        {{ country.count.toLocaleString() }}
                    </span>
                </li>
            </ul>
        </div>

        <p v-else class="flex min-h-[200px] items-center justify-center text-sm text-slate-400">
            No data available
        </p>
    </div>
</template>
