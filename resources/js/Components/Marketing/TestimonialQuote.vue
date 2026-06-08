<script setup>
defineProps({
    quote: { type: String, required: true },
    name: { type: String, required: true },
    role: { type: String, required: true },
    initial: { type: String, required: true },
    color: { type: String, default: 'bg-indigo-100 text-indigo-700' },
});
</script>

<template>
    <div class="vm-quote-block">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full" :class="color">
            <span class="text-sm font-bold">{{ initial }}</span>
        </div>
        <blockquote class="mt-5 font-serif text-lg leading-relaxed text-warm-800 sm:text-xl">
            "{{ quote }}"
        </blockquote>
        <p class="mt-4 text-sm font-semibold text-warm-800">{{ name }}</p>
        <p class="text-xs text-slate-500">{{ role }}</p>
    </div>
</template>
