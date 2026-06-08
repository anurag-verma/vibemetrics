<script setup>
defineProps({
    variant: {
        type: String,
        default: 'hero',
        validator: (v) => ['hero', 'indigo', 'violet', 'cta'].includes(v),
    },
});
</script>

<template>
    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <template v-if="variant === 'hero'">
            <div class="vm-orb vm-orb-indigo -left-24 top-0 h-[28rem] w-[28rem]" />
            <div class="vm-orb vm-orb-violet -right-16 top-1/4 h-[24rem] w-[24rem]" style="animation-delay: 2s" />
            <div class="vm-orb vm-orb-sky bottom-0 left-1/3 h-80 w-80" style="animation-delay: 4s" />
            <div class="vm-dot-grid absolute inset-0 opacity-40" />
        </template>

        <template v-else-if="variant === 'indigo'">
            <div class="vm-orb vm-orb-indigo -left-32 top-1/2 h-96 w-96 -translate-y-1/2 opacity-50" />
            <div class="vm-dot-grid absolute inset-0 opacity-30" />
        </template>

        <template v-else-if="variant === 'violet'">
            <div class="vm-orb vm-orb-violet -right-24 top-0 h-80 w-80 opacity-60" />
            <div class="vm-orb vm-orb-indigo bottom-0 left-1/4 h-72 w-72 opacity-40" style="animation-delay: 3s" />
        </template>

        <template v-else-if="variant === 'cta'">
            <div class="vm-orb vm-orb-indigo left-1/2 top-1/2 h-[32rem] w-[32rem] -translate-x-1/2 -translate-y-1/2 opacity-70" />
            <div class="vm-orb vm-orb-violet -right-20 top-0 h-64 w-64 opacity-50" style="animation-delay: 2s" />
        </template>
    </div>
</template>
