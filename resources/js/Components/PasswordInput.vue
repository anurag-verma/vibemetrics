<script setup>
import PasswordRequirements from '@/Components/PasswordRequirements.vue';
import { onMounted, ref } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const model = defineModel({
    type: String,
    required: true,
});

defineProps({
    id: {
        type: String,
        default: undefined,
    },
    autocomplete: {
        type: String,
        default: 'current-password',
    },
    showRequirements: {
        type: Boolean,
        default: false,
    },
    inputClass: {
        type: String,
        default: '',
    },
});

const input = ref(null);
const showPassword = ref(false);

const onInput = (event) => {
    model.value = event.target.value;
};

const syncAutofill = () => {
    if (input.value && input.value.value !== model.value) {
        model.value = input.value.value;
    }
};

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value.focus();
    }

    syncAutofill();
    window.setTimeout(syncAutofill, 100);
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <div>
        <div class="relative">
            <input
                :id="id"
                ref="input"
                :value="model"
                :type="showPassword ? 'text' : 'password'"
                :autocomplete="autocomplete"
                class="vm-input block w-full rounded-md pr-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                :class="inputClass"
                v-bind="$attrs"
                @input="onInput"
                @change="onInput"
            />
            <button
                type="button"
                class="absolute inset-y-0 right-0 flex items-center rounded-r-md px-3 text-slate-500 transition hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                @click="showPassword = ! showPassword"
            >
                <svg v-if="showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858 3.293a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
        </div>

        <PasswordRequirements
            v-if="showRequirements"
            :password="model"
            :visible="model.length > 0"
        />
    </div>
</template>
