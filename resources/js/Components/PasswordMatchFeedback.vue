<script setup>
import { computed } from 'vue';

const props = defineProps({
    password: {
        type: String,
        default: '',
    },
    confirmation: {
        type: String,
        default: '',
    },
});

const show = computed(() => props.confirmation.length > 0);

const matches = computed(() => props.password === props.confirmation);
</script>

<template>
    <Transition
        enter-active-class="transition duration-150 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <p
            v-if="show"
            class="mt-2 flex items-center gap-1.5 text-xs font-medium"
            :class="matches ? 'text-emerald-600' : 'text-red-600'"
            aria-live="polite"
        >
            <svg
                v-if="matches"
                class="h-3.5 w-3.5 shrink-0"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2.5"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <svg
                v-else
                class="h-3.5 w-3.5 shrink-0"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2.5"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            {{ matches ? 'Passwords match' : 'Passwords do not match' }}
        </p>
    </Transition>
</template>
