<script setup>
import { computed } from 'vue';
import { resolveAnalyticsIcon } from '@/utils/analyticsIcons';

const props = defineProps({
    type: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        default: '',
    },
});

const icon = computed(() => resolveAnalyticsIcon(props.type, props.label));
</script>

<template>
    <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center">
        <span
            v-if="icon?.kind === 'flag'"
            :class="[icon.value, 'analytics-icon-flag']"
            aria-hidden="true"
        />
        <img
            v-else-if="icon?.kind === 'image'"
            :src="icon.value"
            alt=""
            class="analytics-icon-image"
        />
        <!-- Apple (iOS / macOS) -->
        <svg
            v-else-if="icon?.kind === 'glyph' && icon.value === 'apple'"
            viewBox="0 0 24 24"
            class="analytics-icon-glyph text-slate-700 dark:text-slate-200"
            aria-hidden="true"
        >
            <path
                fill="currentColor"
                d="M16.365 1.43c.09 1.02-.3 2.04-.96 2.85-.66.8-1.74 1.42-2.8 1.33-.11-.98.36-2.01 1-2.78.74-.87 2.05-1.46 2.76-1.4ZM20.39 17.07c-.57 1.3-.85 1.88-1.58 3.03-1.02 1.57-2.46 3.53-4.25 3.55-.8.01-1.12-.52-2.33-.51-1.22.01-1.56.52-2.36.51-1.79-.02-3.15-1.85-4.17-3.42-2.87-4.14-3.17-8.99-1.4-11.57.88-1.28 2.27-2.03 3.74-2.05 1.17-.02 2.27.78 2.33.78.06 0 1.98-.96 3.34-.82.57.02 2.17.23 3.2 1.75-2.8 1.53-2.35 5.51.45 6.55-.57 1.66-1.3 3.31-1.97 4.2Z"
            />
        </svg>
        <!-- Windows -->
        <svg
            v-else-if="icon?.kind === 'glyph' && icon.value === 'windows'"
            viewBox="0 0 24 24"
            class="analytics-icon-glyph text-slate-700 dark:text-slate-200"
            aria-hidden="true"
        >
            <path fill="currentColor" d="M3 4.5 10.5 3.4V11H3V4.5Zm0 7.5h7.5v7.6L3 18.5V12ZM11.25 3.2 21 1.5v9.75H11.25V3.2ZM11.25 12.75H21V22l-9.75-1.65V12.75Z" />
        </svg>
        <!-- Linux -->
        <svg
            v-else-if="icon?.kind === 'glyph' && icon.value === 'linux'"
            viewBox="0 0 24 24"
            class="analytics-icon-glyph text-slate-700 dark:text-slate-200"
            aria-hidden="true"
        >
            <path
                fill="currentColor"
                d="M12.03 3c-3.1.04-5.55 2.5-5.78 5.64-.17 2.22.72 4.2 2.2 5.52-.47.62-.9 1.35-1.18 2.12-.34.95-.47 1.96-.28 2.88.42 2.08 2.28 3.58 4.55 3.84.47.74 1.1 1.35 1.86 1.74 1.05.57 2.3.82 3.58.66 2.45-.3 4.4-2.18 4.86-4.58.14-.7.12-1.4-.04-2.06-.2-.84-.58-1.6-1.08-2.24 1.3-1.24 2.08-3.02 2.08-5.04C22.5 5.57 17.74 2.95 12.03 3Zm-.2 1.8c4.7-.2 8.42 2.05 8.42 4.94 0 1.55-.68 2.94-1.82 3.9-.3-.04-.6-.06-.92-.06-2.35 0-4.35 1.55-4.95 3.68-.52-.35-.98-.8-1.34-1.32-.9-1.24-1.2-2.82-.76-4.28.3-1 .9-1.86 1.68-2.48-1.2-1.05-1.94-2.58-1.94-4.3 0-1.06.28-2.06.78-2.94.7.58 1.55.95 2.48 1.06.2-1.05.95-1.95 2.02-2.36-.55-.18-1.12-.28-1.7-.3Zm-3.1 8.55c.62 0 1.12.5 1.12 1.12s-.5 1.12-1.12 1.12-1.12-.5-1.12-1.12.5-1.12 1.12-1.12Zm6.54 0c.62 0 1.12.5 1.12 1.12s-.5 1.12-1.12 1.12-1.12-.5-1.12-1.12.5-1.12 1.12-1.12Z"
            />
        </svg>
        <!-- Desktop -->
        <svg
            v-else-if="icon?.kind === 'glyph' && icon.value === 'desktop'"
            viewBox="0 0 24 24"
            class="analytics-icon-glyph text-slate-600 dark:text-slate-300"
            aria-hidden="true"
        >
            <path
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.75"
                d="M4 5.5h16v10H4V5.5Zm2 12.5h12M12 16v2"
            />
        </svg>
        <!-- Mobile -->
        <svg
            v-else-if="icon?.kind === 'glyph' && icon.value === 'mobile'"
            viewBox="0 0 24 24"
            class="analytics-icon-glyph text-slate-600 dark:text-slate-300"
            aria-hidden="true"
        >
            <path
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.75"
                d="M9 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm3 15.5h.01"
            />
        </svg>
        <!-- Tablet -->
        <svg
            v-else-if="icon?.kind === 'glyph' && icon.value === 'tablet'"
            viewBox="0 0 24 24"
            class="analytics-icon-glyph text-slate-600 dark:text-slate-300"
            aria-hidden="true"
        >
            <path
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.75"
                d="M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm6 13h.01"
            />
        </svg>
        <span
            v-else
            class="inline-block h-4 w-4 rounded bg-slate-200 dark:bg-slate-700"
            aria-hidden="true"
        />
    </span>
</template>
