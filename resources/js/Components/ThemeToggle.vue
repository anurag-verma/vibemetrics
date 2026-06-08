<script setup>
import { getStoredTheme, setStoredTheme } from '@/Composables/useTheme';
import { ref } from 'vue';

const modes = [
    { value: 'system', label: 'System', icon: 'monitor' },
    { value: 'light', label: 'Light', icon: 'sun' },
    { value: 'dark', label: 'Dark', icon: 'moon' },
];

const current = ref(getStoredTheme());
const open = ref(false);

const select = (mode) => {
    current.value = mode;
    setStoredTheme(mode);
    open.value = false;
};

const label = () => modes.find((m) => m.value === current.value)?.label ?? 'System';
</script>

<template>
    <div class="relative">
        <button
            type="button"
            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
            @click="open = !open"
        >
            <svg v-if="current === 'dark'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg v-else-if="current === 'light'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span>{{ label() }}</span>
        </button>
        <div
            v-if="open"
            class="absolute bottom-full left-0 z-50 mb-1 w-full min-w-[140px] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-800"
        >
            <button
                v-for="mode in modes"
                :key="mode.value"
                type="button"
                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition hover:bg-slate-50 dark:hover:bg-slate-700"
                :class="current === mode.value ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-700 dark:text-slate-300'"
                @click="select(mode.value)"
            >
                {{ mode.label }}
            </button>
        </div>
    </div>
</template>
