<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button
        :type="type"
        class="vm-btn-primary inline-flex items-center justify-center disabled:opacity-50"
    >
        <slot />
    </button>
</template>
