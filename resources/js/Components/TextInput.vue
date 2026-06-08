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

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value.focus();
    }
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
    />
</template>
