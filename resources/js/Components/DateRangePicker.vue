<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useViewportDropdownPosition, isClickInsideDropdown } from '@/Composables/useViewportDropdownPosition';
import { useEscapeKey } from '@/Composables/useEscapeKey';
import { DATE_RANGE_GROUPS } from '@/data/dateRanges';
import { router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    dateRange: {
        type: Object,
        required: true,
    },
    routeName: {
        type: String,
        required: true,
    },
    routeParams: {
        type: Object,
        default: () => ({}),
    },
});

const open = ref(false);
const root = ref(null);
const panel = ref(null);
const showCustom = ref(false);
const customFrom = ref(props.dateRange.from ?? '');
const customTo = ref(props.dateRange.to ?? '');

const { panelStyle, updatePosition } = useViewportDropdownPosition(root, panel, open, { width: 256 });

const currentLabel = computed(() => props.dateRange.label ?? 'Last 30 days');

const buildParams = (preset, from = null, to = null) => {
    const params = { preset };

    if (preset === 'custom' && from && to) {
        params.from = from;
        params.to = to;
    }

    return params;
};

const navigate = (params) => {
    open.value = false;
    showCustom.value = false;

    router.get(route(props.routeName, props.routeParams), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const selectPreset = (preset) => {
    if (preset === 'custom') {
        showCustom.value = true;
        customFrom.value = props.dateRange.from ?? '';
        customTo.value = props.dateRange.to ?? '';
        updatePosition();
        return;
    }

    navigate(buildParams(preset));
};

const applyCustom = () => {
    if (!customFrom.value || !customTo.value) {
        return;
    }

    navigate(buildParams('custom', customFrom.value, customTo.value));
};

const toggleOpen = () => {
    open.value = !open.value;

    if (!open.value) {
        showCustom.value = false;
    }
};

const closePanel = () => {
    open.value = false;
    showCustom.value = false;
};

useEscapeKey(open, closePanel);

const onClickOutside = (event) => {
    if (isClickInsideDropdown(event, root, panel)) {
        return;
    }

    closePanel();
};

onMounted(() => document.addEventListener('mousedown', onClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside));
</script>

<template>
    <div ref="root" class="relative">
        <button
            id="date-range-picker-trigger"
            type="button"
            class="inline-flex max-w-[11rem] items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 sm:max-w-none dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
            :aria-expanded="open"
            aria-haspopup="listbox"
            aria-controls="date-range-picker-panel"
            :aria-label="`Date range: ${currentLabel}`"
            @click.stop="toggleOpen"
        >
            <span class="truncate">{{ currentLabel }}</span>
            <svg
                class="h-4 w-4 shrink-0 text-slate-400 transition"
                :class="{ 'rotate-180': open }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <Teleport to="body">
            <div
                v-if="open"
                id="date-range-picker-panel"
                ref="panel"
                role="listbox"
                aria-labelledby="date-range-picker-trigger"
                :style="panelStyle"
                class="w-64 max-w-[calc(100vw-1rem)] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-800"
            >
                <template v-if="!showCustom">
                    <div class="max-h-80 overflow-y-auto py-1">
                        <div
                            v-for="(group, groupIndex) in DATE_RANGE_GROUPS"
                            :key="groupIndex"
                            :class="groupIndex > 0 ? 'border-t border-slate-100 dark:border-slate-700' : ''"
                        >
                            <button
                                v-for="item in group.items"
                                :key="item.value"
                                type="button"
                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition hover:bg-slate-50 dark:hover:bg-slate-700"
                                :class="dateRange.preset === item.value ? 'font-medium text-indigo-600 dark:text-indigo-400' : 'text-slate-700 dark:text-slate-300'"
                                @click="selectPreset(item.value)"
                            >
                                <svg
                                    v-if="dateRange.preset === item.value"
                                    class="h-4 w-4 shrink-0"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span :class="dateRange.preset !== item.value ? 'pl-6' : ''">{{ item.label }}</span>
                            </button>
                        </div>
                    </div>
                </template>

                <div v-else class="space-y-3 p-3">
                    <p class="text-sm font-medium text-slate-900 dark:text-white">Custom range</p>
                    <div>
                        <label for="date-range-custom-from" class="mb-1 block text-xs text-slate-500 dark:text-slate-400">From</label>
                        <input id="date-range-custom-from" v-model="customFrom" type="date" class="vm-input w-full">
                    </div>
                    <div>
                        <label for="date-range-custom-to" class="mb-1 block text-xs text-slate-500 dark:text-slate-400">To</label>
                        <input id="date-range-custom-to" v-model="customTo" type="date" class="vm-input w-full">
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <SecondaryButton type="button" @click="showCustom = false">
                            Back
                        </SecondaryButton>
                        <PrimaryButton type="button" @click="applyCustom">
                            Apply
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
