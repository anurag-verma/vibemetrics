<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    delay: { type: Number, default: 0 },
    direction: {
        type: String,
        default: 'up',
        validator: (v) => ['up', 'down', 'left', 'right', 'scale'].includes(v),
    },
    once: { type: Boolean, default: true },
});

const root = ref(null);
const visible = ref(false);
let observer = null;

onMounted(() => {
    if (!root.value) return;

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReduced) {
        visible.value = true;
        return;
    }

    observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) {
                visible.value = true;
                if (props.once) observer?.disconnect();
            } else if (!props.once) {
                visible.value = false;
            }
        },
        { threshold: 0.12, rootMargin: '0px 0px -48px 0px' },
    );

    observer.observe(root.value);
});

onUnmounted(() => observer?.disconnect());
</script>

<template>
    <div
        ref="root"
        :class="[
            'vm-reveal',
            `vm-reveal-${direction}`,
            visible && 'vm-reveal-visible',
        ]"
        :style="{ transitionDelay: `${delay}ms` }"
    >
        <slot />
    </div>
</template>
