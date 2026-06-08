<script setup>
import ErrorPageShell from '@/Components/ErrorPageShell.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: Number,
        required: true,
    },
});

const content = computed(() => {
    const map = {
        403: {
            title: 'Access denied',
            message: "You don't have permission to view this page. If you believe this is a mistake, contact your administrator.",
        },
        404: {
            title: 'This page wandered off',
            message: "The link may be broken, or the page may have been moved. Let's get you back on track.",
        },
        500: {
            title: 'Something went wrong',
            message: "We're working on it. Please try again in a moment, or head back to the homepage.",
        },
        503: {
            title: "We'll be right back",
            message: "VibeMetrics is temporarily unavailable while we perform maintenance. Thanks for your patience.",
        },
    };

    return map[props.status] ?? {
        title: 'Unexpected error',
        message: 'An error occurred while loading this page. Please try again or return to the homepage.',
    };
});
</script>

<template>
    <ErrorPageShell
        :status="status"
        :title="content.title"
        :message="content.message"
    />
    <Head :title="content.title" />
</template>
