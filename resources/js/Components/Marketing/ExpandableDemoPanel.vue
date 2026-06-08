<script setup>
import { ref } from 'vue';

const props = defineProps({
    label: { type: String, required: true },
    defaultOpen: { type: Boolean, default: false },
});

const open = ref(props.defaultOpen);

const toggle = () => {
    open.value = !open.value;
};
</script>

<template>
    <div class="vm-expand-panel">
        <button
            type="button"
            class="vm-expand-panel-trigger"
            :aria-expanded="open"
            @click="toggle"
        >
            <span class="text-sm font-semibold text-warm-800">{{ label }}</span>
            <span :class="open ? 'vm-expand-panel-icon-open' : ''" class="vm-expand-panel-icon">
                {{ open ? '−' : '+' }}
            </span>
        </button>
        <div :class="open ? 'vm-expand-panel-content-open' : ''" class="vm-expand-panel-content">
            <div class="vm-expand-panel-inner">
                <div class="border-t border-warm-200/80 px-5 pb-5 pt-4">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
