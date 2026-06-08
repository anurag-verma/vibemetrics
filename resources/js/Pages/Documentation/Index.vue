<script setup>
import DocumentationContent from '@/Components/Documentation/Content.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    appUrl: { type: String, required: true },
});
</script>

<template>
    <Head title="Documentation" />

    <AppLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Documentation</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Set up tracking and get the most from your dashboard</p>
            </div>
        </template>

        <DocumentationContent :app-url="appUrl" />
    </AppLayout>
</template>
