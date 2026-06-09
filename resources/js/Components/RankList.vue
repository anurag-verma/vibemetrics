<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: { type: String, default: '' },
    items: { type: Array, default: () => [] },
    emptyText: { type: String, default: 'No data available' },
    labelType: { type: String, default: 'auto' },
    bare: { type: Boolean, default: false },
});

const total = computed(() => props.items.reduce((sum, item) => sum + item.count, 0));

const regionNames = typeof Intl !== 'undefined'
    ? new Intl.DisplayNames(['en'], { type: 'region' })
    : null;

const formatLabel = (label) => {
    if (!label) {
        return '(direct)';
    }

    if (props.labelType === 'country' && label.length === 2 && regionNames) {
        if (label.toUpperCase() === 'XX') {
            return 'Unknown';
        }

        return regionNames.of(label.toUpperCase()) ?? label;
    }

    if (props.labelType === 'referrer' || props.labelType === 'auto') {
        try {
            const url = new URL(label.startsWith('http') ? label : `https://${label}`);
            return url.hostname.replace(/^www\./, '');
        } catch {
            // fall through
        }
    }

    if (props.labelType === 'path' || props.labelType === 'auto') {
        try {
            const url = new URL(label);
            return url.pathname + url.search + url.hash;
        } catch {
            // fall through
        }
    }

    return label.length > 48 ? `${label.slice(0, 48)}…` : label;
};

const sharePercent = (count) => {
    if (!total.value) {
        return 0;
    }

    return Math.round((count / total.value) * 100);
};
</script>

<template>
    <div :class="bare ? 'h-full' : 'vm-card h-full min-h-[240px] animate-fade-in'">
        <h3 v-if="title" class="vm-panel-title mb-4">{{ title }}</h3>
        <ul v-if="items.length" class="space-y-3">
            <li
                v-for="(item, index) in items"
                :key="index"
                class="rounded-lg px-2 py-2 transition hover:bg-slate-50 dark:hover:bg-slate-800"
            >
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-2">
                        <span class="w-4 shrink-0 text-xs font-medium text-slate-400">{{ index + 1 }}</span>
                        <span class="truncate text-sm text-slate-700 dark:text-slate-300" :title="item.label">
                            {{ formatLabel(item.label) }}
                        </span>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <span class="text-xs text-slate-400">{{ sharePercent(item.count) }}%</span>
                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                            {{ item.count.toLocaleString() }}
                        </span>
                    </div>
                </div>
                <div class="mt-1.5 ml-6 h-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div
                        class="h-full rounded-full bg-indigo-500 transition-all dark:bg-indigo-400"
                        :style="{ width: `${sharePercent(item.count)}%` }"
                    />
                </div>
            </li>
        </ul>
        <p v-else class="flex min-h-[180px] items-center justify-center py-8 text-center text-sm text-slate-400">{{ emptyText }}</p>
    </div>
</template>
