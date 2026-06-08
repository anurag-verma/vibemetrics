<script setup>
const integrations = [
    { name: 'WordPress', color: 'bg-sky-50 text-sky-700', letter: 'W' },
    { name: 'Shopify', color: 'bg-emerald-50 text-emerald-700', letter: 'S' },
    { name: 'Ghost', color: 'bg-slate-100 text-slate-700', letter: 'G' },
    { name: 'Webflow', color: 'bg-indigo-50 text-indigo-700', letter: 'W' },
];
</script>

<template>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div
            v-for="item in integrations"
            :key="item.name"
            class="vm-craft-card-interactive flex flex-col items-center justify-center p-6 text-center"
        >
            <span :class="item.color" class="flex h-12 w-12 items-center justify-center rounded-xl text-lg font-bold">
                {{ item.letter }}
            </span>
            <p class="mt-3 text-sm font-semibold text-warm-800">{{ item.name }}</p>
        </div>
    </div>
</template>
