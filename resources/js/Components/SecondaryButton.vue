<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="vm-btn-secondary inline-flex items-center justify-center disabled:opacity-50"
    >
        <slot />
    </button>
</template>
