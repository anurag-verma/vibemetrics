<script setup>
defineProps({
    modelValue: {
        type: Boolean,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <button
        type="button"
        role="switch"
        :aria-checked="modelValue"
        :aria-label="label"
        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition disabled:cursor-not-allowed disabled:opacity-50"
        :class="modelValue ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700'"
        :disabled="disabled"
        @click="$emit('update:modelValue', !modelValue)"
    >
        <span
            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition"
            :class="modelValue ? 'translate-x-5' : 'translate-x-0'"
        />
    </button>
</template>
