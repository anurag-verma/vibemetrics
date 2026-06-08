<script setup>
import { useBranding } from '@/Composables/useBranding';
import { Link } from '@inertiajs/vue3';

defineProps({
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['nav', 'sm', 'md', 'lg', 'xl'].includes(v),
    },
    href: {
        type: String,
        default: '/',
    },
});

const { branding } = useBranding();

/** nav = compact header; sm/md/lg/xl scale up for auth, footer, hero */
const sizes = {
    nav: 'h-9 max-h-10',
    sm: 'h-10',
    md: 'h-12',
    lg: 'h-16',
    xl: 'h-20',
};
</script>

<template>
    <Link :href="href" class="inline-flex shrink-0 items-center transition opacity-100 hover:opacity-90">
        <img
            :src="branding.siteLogoUrl"
            :alt="branding.appName"
            :class="[sizes[size], 'w-auto object-contain']"
        />
    </Link>
</template>
