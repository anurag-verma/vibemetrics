<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <div class="vm-page-header">
        <div class="min-w-0 flex-1">
            <h1 class="vm-page-title">{{ title }}</h1>
            <p v-if="description" class="vm-page-description">
                {{ description }}
            </p>
            <slot name="description" />
        </div>
        <div v-if="$slots.actions" class="vm-page-actions">
            <slot name="actions" />
        </div>
    </div>
</template>
