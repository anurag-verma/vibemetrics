<script setup>
import { router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    modelValue: { type: Number, default: 30 },
    siteId: { type: Number, required: true },
});

const ranges = [
    { label: 'Last 7 days', value: 7 },
    { label: 'Last 30 days', value: 30 },
    { label: 'Last 90 days', value: 90 },
];

const open = ref(false);
const root = ref(null);

const currentLabel = computed(
    () => ranges.find((r) => r.value === props.modelValue)?.label ?? 'Last 30 days',
);

const select = (value) => {
    open.value = false;
    router.get(route('sites.show', props.siteId), { range: value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const onClickOutside = (e) => {
    if (root.value && !root.value.contains(e.target)) {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));
</script>

<template>
    <div ref="root" class="relative">
        <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
            @click.stop="open = !open"
        >
            {{ currentLabel }}
            <svg
                class="h-4 w-4 text-slate-400 transition"
                :class="{ 'rotate-180': open }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div
            v-if="open"
            class="absolute right-0 z-50 mt-1 min-w-[180px] overflow-hidden rounded-lg border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-800"
        >
            <button
                v-for="r in ranges"
                :key="r.value"
                type="button"
                class="flex w-full items-center justify-between px-4 py-2 text-left text-sm transition hover:bg-slate-50 dark:hover:bg-slate-700"
                :class="modelValue === r.value ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-700 dark:text-slate-300'"
                @click="select(r.value)"
            >
                {{ r.label }}
                <svg
                    v-if="modelValue === r.value"
                    class="h-4 w-4 shrink-0"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </button>
        </div>
    </div>
</template>
