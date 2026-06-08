<script setup>
import { computed } from 'vue';

const props = defineProps({
    cells: { type: Array, default: () => [] },
});

const dayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const hourLabels = ['12am', '3am', '6am', '9am', '12pm', '3pm', '6pm', '9pm'];

const maxCount = computed(() => {
    const peak = Math.max(...props.cells.map((cell) => cell.count), 0);
    return peak > 0 ? peak : 1;
});

const cellMap = computed(() => {
    const map = {};

    props.cells.forEach((cell) => {
        map[`${cell.day}-${cell.hour}`] = cell.count;
    });

    return map;
});

const intensity = (count) => {
    if (count === 0) {
        return 0;
    }

    return 0.15 + (count / maxCount.value) * 0.85;
};
</script>

<template>
    <div class="vm-card h-full min-h-[280px] animate-fade-in">
        <h3 class="vm-panel-title mb-4">Traffic</h3>

        <div v-if="cells.some((cell) => cell.count > 0)" class="overflow-x-auto">
            <div class="min-w-[520px]">
                <div class="mb-2 grid grid-cols-[2.5rem_repeat(24,1fr)] gap-0.5">
                    <div />
                    <div
                        v-for="hour in 24"
                        :key="hour"
                        class="text-center text-[9px] text-slate-400"
                    >
                        <span v-if="(hour - 1) % 3 === 0">{{ hourLabels[Math.floor((hour - 1) / 3)] ?? '' }}</span>
                    </div>
                </div>

                <div
                    v-for="(dayLabel, dayIndex) in dayLabels"
                    :key="dayLabel"
                    class="mb-0.5 grid grid-cols-[2.5rem_repeat(24,1fr)] items-center gap-0.5"
                >
                    <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">{{ dayLabel }}</span>
                    <div
                        v-for="hour in 24"
                        :key="`${dayIndex}-${hour}`"
                        class="aspect-square rounded-sm"
                        :title="`${dayLabel} ${hour - 1}:00 — ${cellMap[`${dayIndex}-${hour - 1}`] ?? 0} views`"
                        :style="{
                            backgroundColor: (cellMap[`${dayIndex}-${hour - 1}`] ?? 0) > 0
                                ? `rgba(99, 102, 241, ${intensity(cellMap[`${dayIndex}-${hour - 1}`] ?? 0)})`
                                : 'rgba(148, 163, 184, 0.12)',
                        }"
                    />
                </div>
            </div>
        </div>

        <p v-else class="flex min-h-[200px] items-center justify-center text-sm text-slate-400">
            No data available
        </p>
    </div>
</template>
