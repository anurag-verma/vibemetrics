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

const cellCount = (dayIndex, hour) => cellMap.value[`${dayIndex}-${hour}`] ?? 0;

const intensity = (count) => {
    if (count === 0) {
        return 0;
    }

    return 0.15 + (count / maxCount.value) * 0.85;
};

const cellLabel = (dayLabel, hour) => `${dayLabel} ${hour}:00, ${cellCount(dayLabels.indexOf(dayLabel), hour)} views`;
</script>

<template>
    <div class="vm-card h-full min-h-[280px] animate-fade-in">
        <h3 class="vm-panel-title mb-4">Traffic</h3>

        <div v-if="cells.some((cell) => cell.count > 0)" class="overflow-x-auto">
            <table class="min-w-[520px] border-collapse" role="grid" aria-label="Traffic heatmap by day and hour">
                <thead>
                    <tr>
                        <th class="w-10" scope="col"><span class="sr-only">Day</span></th>
                        <th
                            v-for="hour in 24"
                            :key="`head-${hour}`"
                            scope="col"
                            class="px-0 text-center text-[9px] font-normal text-slate-400"
                        >
                            <span v-if="(hour - 1) % 3 === 0">{{ hourLabels[Math.floor((hour - 1) / 3)] ?? '' }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(dayLabel, dayIndex) in dayLabels" :key="dayLabel">
                        <th scope="row" class="pr-1 text-left text-[10px] font-medium text-slate-500 dark:text-slate-400">
                            {{ dayLabel }}
                        </th>
                        <td
                            v-for="hour in 24"
                            :key="`${dayIndex}-${hour}`"
                            role="gridcell"
                            class="p-0.5"
                            :aria-label="cellLabel(dayLabel, hour - 1)"
                        >
                            <div
                                class="aspect-square rounded-sm"
                                :style="{
                                    backgroundColor: cellCount(dayIndex, hour - 1) > 0
                                        ? `rgba(99, 102, 241, ${intensity(cellCount(dayIndex, hour - 1))})`
                                        : 'rgba(148, 163, 184, 0.12)',
                                }"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-else class="flex min-h-[200px] items-center justify-center text-sm text-slate-400">
            No data available
        </p>
    </div>
</template>
