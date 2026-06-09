<script setup>
import { onMounted, ref } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

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
    <input
        ref="input"
        :value="model"
        class="vm-input rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        v-bind="$attrs"
        @input="onInput"
        @change="onInput"
    />
</template>
