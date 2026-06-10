<script setup>
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    id: { type: String, default: undefined },
    placeholder: { type: String, default: '' },
    maxLength: { type: Number, default: 500 },
});

const emit = defineEmits(['update:modelValue']);

const editor = ref(null);
const showPlaceholder = ref(true);

const toolbar = [
    { command: 'bold', label: 'Bold', title: 'Bold (Ctrl+B)' },
    { command: 'italic', label: 'Italic', title: 'Italic (Ctrl+I)' },
    { command: 'underline', label: 'Underline', title: 'Underline (Ctrl+U)' },
];

const charCount = computed(() => editor.value?.textContent?.length ?? 0);

const syncFromModel = () => {
    if (!editor.value) {
        return;
    }

    if (editor.value.innerHTML !== props.modelValue) {
        editor.value.innerHTML = props.modelValue || '';
    }

    updatePlaceholder();
};

const updatePlaceholder = () => {
    const text = editor.value?.textContent?.trim() ?? '';
    showPlaceholder.value = text === '';
};

const emitChange = () => {
    if (!editor.value) {
        return;
    }

    emit('update:modelValue', editor.value.innerHTML);
    updatePlaceholder();
};

const runCommand = (command) => {
    if (props.disabled) {
        return;
    }

    editor.value?.focus();
    document.execCommand(command, false);
    emitChange();
};

const onInput = () => {
    if (!editor.value) {
        return;
    }

    const text = editor.value.textContent ?? '';

    if (text.length > props.maxLength) {
        document.execCommand('undo');
        return;
    }

    emitChange();
};

const onPaste = (event) => {
    event.preventDefault();

    const text = event.clipboardData?.getData('text/plain') ?? '';
    const currentLength = editor.value?.textContent?.length ?? 0;
    const allowed = props.maxLength - currentLength;
    const slice = allowed > 0 ? text.slice(0, allowed) : '';

    if (slice !== '') {
        document.execCommand('insertText', false, slice);
    }

    emitChange();
};

const onKeydown = (event) => {
    if (!event.ctrlKey && !event.metaKey) {
        return;
    }

    const shortcuts = {
        b: 'bold',
        i: 'italic',
        u: 'underline',
    };

    const command = shortcuts[event.key.toLowerCase()];

    if (!command) {
        return;
    }

    event.preventDefault();
    runCommand(command);
};

const focusEditor = () => {
    if (!props.disabled) {
        editor.value?.focus();
    }
};

onMounted(syncFromModel);
watch(() => props.modelValue, syncFromModel);
watch(() => props.disabled, () => {
    if (editor.value) {
        editor.value.contentEditable = props.disabled ? 'false' : 'true';
    }
});
</script>

<template>
    <div
        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-slate-600 dark:bg-slate-800"
        :class="{ 'opacity-60': disabled }"
    >
        <div class="flex flex-wrap gap-1 border-b border-slate-200 bg-slate-50 px-2 py-1.5 dark:border-slate-600 dark:bg-slate-900/60">
            <button
                v-for="item in toolbar"
                :key="item.command"
                type="button"
                class="rounded-md px-2.5 py-1 text-xs font-semibold text-slate-600 transition hover:bg-white hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-50 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
                :title="item.title"
                :disabled="disabled"
                @click="runCommand(item.command)"
            >
                <span v-if="item.command === 'bold'">B</span>
                <span v-else-if="item.command === 'italic'" class="italic">I</span>
                <span v-else-if="item.command === 'underline'" class="underline">U</span>
            </button>
        </div>

        <div class="relative px-3 py-2">
            <div
                v-if="showPlaceholder"
                class="pointer-events-none absolute inset-x-3 top-2 text-sm text-slate-400 dark:text-slate-500"
                aria-hidden="true"
            >
                {{ placeholder }}
            </div>
            <div
                :id="id"
                ref="editor"
                class="rich-text-editor min-h-[5.5rem] text-sm leading-relaxed text-slate-800 outline-none dark:text-slate-100"
                :contenteditable="!disabled"
                role="textbox"
                aria-multiline="true"
                @input="onInput"
                @paste="onPaste"
                @keydown="onKeydown"
                @click="focusEditor"
            />
        </div>

        <div class="border-t border-slate-200 px-3 py-1.5 text-right text-xs text-slate-400 dark:border-slate-600 dark:text-slate-500">
            {{ charCount }} / {{ maxLength }}
        </div>
    </div>
</template>

<style scoped>
.rich-text-editor :deep(strong),
.rich-text-editor :deep(b) {
    font-weight: 700;
}

.rich-text-editor :deep(em),
.rich-text-editor :deep(i) {
    font-style: italic;
}

.rich-text-editor :deep(u) {
    text-decoration: underline;
}
</style>
