<script setup>
import DocumentationContent from '@/Components/Documentation/Content.vue';
import PageHeader from '@/Components/PageHeader.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    appUrl: { type: String, required: true },
});
</script>

<template>
    <Head title="Documentation" />

    <AppLayout>
        <PageHeader
            title="Documentation"
            description="Set up tracking and get the most from your dashboard."
        />
        <DocumentationContent :app-url="appUrl" />
    </AppLayout>
</template>
