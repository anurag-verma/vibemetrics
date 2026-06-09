<script setup>
import { computed } from 'vue';

const props = defineProps({
    sections: {
        type: Array,
        required: true,
    },
    variant: {
        type: String,
        default: 'app',
        validator: (value) => ['app', 'marketing'].includes(value),
    },
});

const stickyTopClass = computed(() =>
    props.variant === 'marketing' ? 'top-16' : 'top-14',
);

const mobileNavClass = computed(() =>
    props.variant === 'marketing'
        ? 'border-warm-200/80 bg-paper/95'
        : 'border-slate-200/80 bg-slate-50/95 dark:border-slate-800 dark:bg-slate-950/95',
);

const linkClass = computed(() =>
    props.variant === 'marketing'
        ? 'block rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-paper hover:text-warm-800'
        : 'block rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white',
);

const chipClass = computed(() =>
    props.variant === 'marketing'
        ? 'shrink-0 rounded-full border border-warm-200 bg-paper px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-indigo-200 hover:bg-white hover:text-warm-800'
        : 'shrink-0 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-indigo-200 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:text-white',
);

const mobileEdgeClass = computed(() =>
    props.variant === 'marketing'
        ? '-mx-4 px-4 sm:-mx-6 sm:px-6'
        : '-mx-4 px-4',
);
</script>

<template>
    <div class="min-w-0">
        <nav class="hidden lg:block">
            <div class="sticky space-y-1" :class="variant === 'marketing' ? 'top-24' : 'top-6'">
                <a
                    v-for="item in sections"
                    :key="item.id"
                    :href="`#${item.id}`"
                    :class="linkClass"
                >
                    {{ item.label }}
                </a>
            </div>
        </nav>

        <div
            class="sticky z-30 mb-6 border-b py-3 backdrop-blur lg:hidden"
            :class="[stickyTopClass, mobileNavClass, mobileEdgeClass]"
        >
            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                On this page
            </p>
            <div class="flex gap-2 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                <a
                    v-for="item in sections"
                    :key="item.id"
                    :href="`#${item.id}`"
                    :class="chipClass"
                >
                    {{ item.label }}
                </a>
            </div>
        </div>
    </div>
</template>
