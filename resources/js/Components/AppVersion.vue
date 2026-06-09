<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'sidebar',
        validator: (value) => ['sidebar', 'inline'].includes(value),
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const version = computed(() => usePage().props.app?.version);
</script>

<template>
    <component
        :is="variant === 'inline' ? 'span' : 'p'"
        v-if="version"
        :class="variant === 'sidebar'
            ? ['px-1 pt-1 text-xs text-slate-500', collapsed ? 'lg:hidden' : '']
            : 'text-slate-400'"
    >
        <template v-if="variant === 'inline'"> · Version {{ version }}</template>
        <template v-else>Version {{ version }}</template>
    </component>
</template>
