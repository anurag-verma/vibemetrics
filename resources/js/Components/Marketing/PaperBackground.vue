<script setup>
defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'alt'].includes(v),
    },
});
</script>

<template>
    <div
        :class="variant === 'alt' ? 'vm-section-mesh-alt' : 'vm-section-mesh'"
        class="absolute inset-0 -z-10"
        aria-hidden="true"
    />
</template>
