<script setup>
defineProps({
    position: {
        type: String,
        default: 'hero',
        validator: (v) => ['hero', 'left', 'right'].includes(v),
    },
});
</script>

<template>
    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <svg
            v-if="position === 'hero'"
            class="absolute -right-8 top-12 h-32 w-48 animate-float-slow opacity-60 sm:h-40 sm:w-56"
            viewBox="0 0 200 120"
            fill="none"
        >
            <ellipse cx="100" cy="70" rx="90" ry="40" fill="white" fill-opacity="0.7" />
            <ellipse cx="70" cy="55" rx="50" ry="30" fill="white" fill-opacity="0.5" />
            <ellipse cx="130" cy="50" rx="45" ry="28" fill="white" fill-opacity="0.5" />
        </svg>
        <svg
            v-if="position === 'hero'"
            class="absolute -left-12 bottom-24 h-24 w-40 animate-float opacity-50"
            viewBox="0 0 200 120"
            fill="none"
            style="animation-delay: 2s"
        >
            <ellipse cx="100" cy="70" rx="80" ry="35" fill="white" fill-opacity="0.6" />
            <ellipse cx="60" cy="58" rx="40" ry="25" fill="white" fill-opacity="0.4" />
        </svg>
        <svg
            v-if="position === 'left'"
            class="absolute -left-16 top-1/3 h-20 w-36 animate-float-slow opacity-40"
            viewBox="0 0 200 120"
            fill="none"
        >
            <ellipse cx="100" cy="70" rx="80" ry="35" fill="white" fill-opacity="0.5" />
        </svg>
        <svg
            v-if="position === 'right'"
            class="absolute -right-12 top-1/4 h-28 w-44 animate-float opacity-45"
            viewBox="0 0 200 120"
            fill="none"
            style="animation-delay: 1.5s"
        >
            <ellipse cx="100" cy="70" rx="85" ry="38" fill="white" fill-opacity="0.55" />
        </svg>
    </div>
</template>
