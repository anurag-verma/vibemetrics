<script setup>
import { computed } from 'vue';

const props = defineProps({
    cells: { type: Array, default: () => [] },
    timezone: { type: String, default: 'UTC' },
});

/** PHP `w` format: 0 = Sunday … 6 = Saturday. Display Mon → Sun (ISO week). */
const displayDays = [
    { index: 1, label: 'Mon', full: 'Monday' },
    { index: 2, label: 'Tue', full: 'Tuesday' },
    { index: 3, label: 'Wed', full: 'Wednesday' },
    { index: 4, label: 'Thu', full: 'Thursday' },
    { index: 5, label: 'Fri', full: 'Friday' },
    { index: 6, label: 'Sat', full: 'Saturday' },
    { index: 0, label: 'Sun', full: 'Sunday' },
];

const hourFormatter = new Intl.DateTimeFormat('en', {
    hour: 'numeric',
    hour12: true,
    timeZone: 'UTC',
});

const timezoneLabel = computed(() => props.timezone.replace(/_/g, ' '));

const maxCount = computed(() => Math.max(...props.cells.map((cell) => cell.count), 0));

const hasData = computed(() => maxCount.value > 0);

const cellMap = computed(() => {
    const map = {};

    props.cells.forEach((cell) => {
        map[`${cell.day}-${cell.hour}`] = cell.count;
    });

    return map;
});

const cellCount = (dayIndex, hour) => cellMap.value[`${dayIndex}-${hour}`] ?? 0;

const formatHour = (hour) => {
    const date = new Date(Date.UTC(2024, 0, 1, hour));

    return hourFormatter.format(date);
};

const formatHourRange = (hour) => {
    const start = formatHour(hour);
    const end = formatHour((hour + 1) % 24);

    return `${start} – ${end}`;
};

const intensityLevel = (count) => {
    if (count === 0 || maxCount.value === 0) {
        return 0;
    }

    const ratio = count / maxCount.value;

    if (ratio <= 0.2) {
        return 1;
    }

    if (ratio <= 0.4) {
        return 2;
    }

    if (ratio <= 0.6) {
        return 3;
    }

    if (ratio <= 0.8) {
        return 4;
    }

    return 5;
};

const cellClass = (count) => {
    const level = intensityLevel(count);

    return [
        'heatmap-cell h-3 w-full min-w-[10px] rounded-[3px] transition-colors sm:h-3.5',
        level === 0 && 'bg-slate-100 dark:bg-slate-800/80',
        level === 1 && 'bg-indigo-200 dark:bg-indigo-950',
        level === 2 && 'bg-indigo-300 dark:bg-indigo-900',
        level === 3 && 'bg-indigo-400 dark:bg-indigo-700',
        level === 4 && 'bg-indigo-500 dark:bg-indigo-500',
        level === 5 && 'bg-indigo-600 dark:bg-indigo-400',
    ].filter(Boolean).join(' ');
};

const legendClass = (level) => {
    const classes = {
        0: 'bg-slate-100 dark:bg-slate-800/80',
        1: 'bg-indigo-200 dark:bg-indigo-950',
        2: 'bg-indigo-300 dark:bg-indigo-900',
        3: 'bg-indigo-400 dark:bg-indigo-700',
        4: 'bg-indigo-500 dark:bg-indigo-500',
        5: 'bg-indigo-600 dark:bg-indigo-400',
    };

    return classes[level] ?? classes[0];
};

const cellLabel = (day, hour) => {
    const count = cellCount(day.index, hour);

    return `${day.full}, ${formatHourRange(hour)} · ${count.toLocaleString()} ${count === 1 ? 'view' : 'views'}`;
};

const showHourLabel = (hour) => hour % 3 === 0;
</script>

<template>
    <div class="vm-card animate-fade-in">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
            <div>
                <h3 class="vm-panel-title">Traffic by time</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Pageviews by day and hour · {{ timezoneLabel }}
                </p>
            </div>
        </div>

        <div v-if="hasData" class="space-y-3">
            <div class="overflow-x-auto pb-1">
                <div
                    class="min-w-[640px] grid gap-[3px]"
                    style="grid-template-columns: 2.25rem repeat(24, minmax(0, 1fr));"
                    role="grid"
                    aria-label="Traffic heatmap by day and hour"
                >
                    <div role="presentation" />

                    <div
                        v-for="hour in 24"
                        :key="`head-${hour}`"
                        role="columnheader"
                        class="flex h-4 items-end justify-center"
                    >
                        <span
                            v-if="showHourLabel(hour - 1)"
                            class="text-[10px] leading-none text-slate-400 dark:text-slate-500"
                        >
                            {{ formatHour(hour - 1) }}
                        </span>
                    </div>

                    <template v-for="day in displayDays" :key="day.label">
                        <div
                            role="rowheader"
                            class="flex items-center pr-1 text-[11px] font-medium text-slate-500 dark:text-slate-400"
                        >
                            {{ day.label }}
                        </div>

                        <div
                            v-for="hour in 24"
                            :key="`${day.index}-${hour}`"
                            role="gridcell"
                            :aria-label="cellLabel(day, hour - 1)"
                        >
                            <div
                                :class="cellClass(cellCount(day.index, hour - 1))"
                                :title="cellLabel(day, hour - 1)"
                            />
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Hover a cell for the exact count
                </p>
                <div class="flex items-center gap-2 text-[11px] text-slate-400 dark:text-slate-500">
                    <span>Less</span>
                    <div class="flex gap-1">
                        <div
                            v-for="level in 6"
                            :key="level"
                            class="h-3 w-3 rounded-[3px]"
                            :class="legendClass(level - 1)"
                        />
                    </div>
                    <span>More</span>
                </div>
            </div>
        </div>

        <p v-else class="flex min-h-[160px] items-center justify-center text-sm text-slate-400">
            No traffic data for this period yet
        </p>
    </div>
</template>
