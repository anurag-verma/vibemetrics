<script setup>
import OsAppIcon from '@/Components/OsAppIcon.vue';
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
        <OsAppIcon
            v-else-if="icon?.kind === 'os-app'"
            :name="icon.value"
        />
        <svg
            v-else-if="icon?.kind === 'brand'"
            viewBox="0 0 24 24"
            class="analytics-icon-brand"
            aria-hidden="true"
        >
            <path :d="icon.path" :fill="`#${icon.hex}`" />
        </svg>
        <!-- Unknown location -->
        <svg
            v-else-if="icon?.kind === 'glyph' && icon.value === 'unknown-location'"
            viewBox="0 0 24 24"
            class="analytics-icon-glyph text-slate-400 dark:text-slate-500"
            aria-hidden="true"
        >
            <path
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.75"
                d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0v-3.5M12 8.5h.01M10.2 10.2a2.2 2.2 0 1 1 3.1 3.1"
            />
        </svg>
        <!-- Unknown OS -->
        <svg
            v-else-if="icon?.kind === 'glyph' && icon.value === 'unknown-os'"
            viewBox="0 0 24 24"
            class="analytics-icon-glyph text-slate-400 dark:text-slate-500"
            aria-hidden="true"
        >
            <path
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.75"
                d="M4 6.5h16v9H4v-9Zm5 12.5h6M9 3.5h6M12 17v1"
            />
            <path
                fill="currentColor"
                d="M12 10.25a.9.9 0 1 0 0 1.8.9.9 0 0 0 0-1.8Zm-.15 2.55h.3v2.2h-.3v-2.2Z"
            />
        </svg>
        <!-- Generic unknown -->
        <svg
            v-else-if="icon?.kind === 'glyph' && icon.value === 'unknown'"
            viewBox="0 0 24 24"
            class="analytics-icon-glyph text-slate-400 dark:text-slate-500"
            aria-hidden="true"
        >
            <path
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.75"
                d="M12 16v-4M12 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
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
    </span>
</template>
