<script setup>
import { computed } from 'vue';
import { getPasswordRequirements } from '@/utils/validation';

const props = defineProps({
    password: {
        type: String,
        default: '',
    },
    visible: {
        type: Boolean,
        default: false,
    },
});

const requirements = computed(() => getPasswordRequirements(props.password));

const items = computed(() => [
    { key: 'minLength', label: '8+ characters', met: requirements.value.minLength },
    { key: 'letters', label: 'A letter', met: requirements.value.letters },
    { key: 'mixedCase', label: 'Upper & lower case', met: requirements.value.mixedCase },
    { key: 'numbers', label: 'A number', met: requirements.value.numbers },
]);

const metCount = computed(() => items.value.filter((item) => item.met).length);

const allMet = computed(() => metCount.value === items.value.length);

const progressPercent = computed(() => Math.round((metCount.value / items.value.length) * 100));

const showPanel = computed(() => props.visible && props.password.length > 0 && ! allMet.value);
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-1"
    >
        <div
            v-if="showPanel"
            class="mt-2.5 overflow-hidden rounded-xl border border-slate-200 bg-gradient-to-b from-slate-50 to-white p-3.5 shadow-sm"
            aria-live="polite"
        >
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="text-xs font-semibold text-slate-700">Password strength</p>
                <span class="text-xs font-medium tabular-nums text-slate-500">{{ metCount }}/{{ items.length }}</span>
            </div>

            <div class="mb-3 h-1.5 overflow-hidden rounded-full bg-slate-200">
                <div
                    class="h-full rounded-full transition-all duration-300 ease-out"
                    :class="progressPercent >= 100 ? 'bg-emerald-500' : progressPercent >= 50 ? 'bg-indigo-500' : 'bg-amber-400'"
                    :style="{ width: `${progressPercent}%` }"
                />
            </div>

            <ul class="grid grid-cols-2 gap-x-3 gap-y-2">
                <li
                    v-for="item in items"
                    :key="item.key"
                    class="flex items-center gap-2"
                >
                    <span
                        class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full transition-colors"
                        :class="item.met
                            ? 'bg-emerald-500 text-white'
                            : 'border border-slate-300 bg-white text-transparent'"
                    >
                        <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    <span
                        class="text-xs leading-tight"
                        :class="item.met ? 'font-medium text-slate-800' : 'text-slate-500'"
                    >
                        {{ item.label }}
                    </span>
                </li>
            </ul>
        </div>
    </Transition>
</template>
