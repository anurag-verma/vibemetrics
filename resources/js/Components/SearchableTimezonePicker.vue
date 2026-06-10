<script setup>
import { isClickInsideDropdown, useViewportDropdownPosition } from '@/Composables/useViewportDropdownPosition';
import { computed, onMounted, onUnmounted, ref, useAttrs, watch } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const attrs = useAttrs();

const props = defineProps({
    options: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'Search timezones…',
    },
});

const model = defineModel({
    type: String,
    required: true,
});

const root = ref(null);
const panel = ref(null);
const search = ref('');
const open = ref(false);
const activeIndex = ref(0);

const { panelStyle } = useViewportDropdownPosition(root, panel, open, {
    matchTriggerWidth: true,
    minWidth: 240,
});

const formatLabel = (timezone) => {
    try {
        const formatter = new Intl.DateTimeFormat('en-US', {
            timeZone: timezone,
            timeZoneName: 'shortOffset',
        });
        const offset = formatter.formatToParts(new Date())
            .find((part) => part.type === 'timeZoneName')?.value ?? '';

        return offset ? `${timezone} (${offset})` : timezone;
    } catch {
        return timezone;
    }
};

const allOptions = computed(() => {
    const list = [...props.options];

    if (model.value && !list.includes(model.value)) {
        list.unshift(model.value);
    }

    return list;
});

const filteredOptions = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return allOptions.value;
    }

    return allOptions.value.filter((timezone) => {
        const label = formatLabel(timezone).toLowerCase();
        return timezone.toLowerCase().includes(query) || label.includes(query);
    });
});

const selectedLabel = computed(() => (
    model.value ? formatLabel(model.value) : ''
));

const openPicker = () => {
    open.value = true;
    search.value = '';
    activeIndex.value = Math.max(0, filteredOptions.value.indexOf(model.value));
};

const closePicker = () => {
    open.value = false;
    search.value = '';
};

const selectTimezone = (timezone) => {
    model.value = timezone;
    closePicker();
};

const onSearchKeydown = (event) => {
    if (!open.value) {
        if (event.key === 'ArrowDown' || event.key === 'Enter') {
            openPicker();
            event.preventDefault();
        }

        return;
    }

    if (event.key === 'Escape') {
        closePicker();
        event.preventDefault();
        return;
    }

    if (event.key === 'ArrowDown') {
        activeIndex.value = Math.min(activeIndex.value + 1, filteredOptions.value.length - 1);
        event.preventDefault();
        return;
    }

    if (event.key === 'ArrowUp') {
        activeIndex.value = Math.max(activeIndex.value - 1, 0);
        event.preventDefault();
        return;
    }

    if (event.key === 'Enter' && filteredOptions.value[activeIndex.value]) {
        selectTimezone(filteredOptions.value[activeIndex.value]);
        event.preventDefault();
    }
};

const onClickOutside = (event) => {
    if (isClickInsideDropdown(event, root, panel)) {
        return;
    }

    closePicker();
};

watch(filteredOptions, (options) => {
    if (activeIndex.value >= options.length) {
        activeIndex.value = Math.max(0, options.length - 1);
    }
});

watch(search, () => {
    activeIndex.value = 0;
});

onMounted(() => document.addEventListener('mousedown', onClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside));
</script>

<template>
    <div ref="root" class="relative w-full">
        <button
            type="button"
            class="vm-input flex w-full items-center justify-between gap-2 text-left transition hover:border-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
            :class="open ? 'border-indigo-500 ring-2 ring-indigo-500/20' : ''"
            :aria-expanded="open"
            aria-haspopup="listbox"
            v-bind="attrs"
            @click="open ? closePicker() : openPicker()"
        >
            <span class="truncate" :class="model ? 'text-slate-900 dark:text-slate-200' : 'text-slate-400'">
                {{ selectedLabel || placeholder }}
            </span>
            <svg
                class="h-4 w-4 shrink-0 text-slate-400 transition"
                :class="open ? 'rotate-180' : ''"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <Teleport to="body">
            <div
                v-if="open"
                ref="panel"
                :style="panelStyle"
                class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900"
            >
                <div class="border-b border-slate-200 p-2 dark:border-slate-700">
                    <input
                        v-model="search"
                        type="search"
                        class="vm-input w-full"
                        :placeholder="placeholder"
                        autocomplete="off"
                        @keydown="onSearchKeydown"
                    >
                </div>

                <ul
                    class="max-h-60 overflow-y-auto py-1"
                    role="listbox"
                >
                    <li
                        v-for="(timezone, index) in filteredOptions"
                        :key="timezone"
                        role="option"
                        :aria-selected="timezone === model"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center px-3 py-2 text-left text-sm transition"
                            :class="[
                                timezone === model ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : '',
                                index === activeIndex && timezone !== model ? 'bg-slate-50 dark:bg-slate-800' : '',
                                timezone !== model && index !== activeIndex ? 'text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800' : '',
                            ]"
                            @mouseenter="activeIndex = index"
                            @click="selectTimezone(timezone)"
                        >
                            <span class="truncate">{{ formatLabel(timezone) }}</span>
                        </button>
                    </li>
                    <li
                        v-if="filteredOptions.length === 0"
                        class="px-3 py-6 text-center text-sm text-slate-400"
                    >
                        No timezones found
                    </li>
                </ul>
            </div>
        </Teleport>
    </div>
</template>
