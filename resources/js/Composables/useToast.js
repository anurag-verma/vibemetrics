import { ref } from 'vue';

const toasts = ref([]);
let nextId = 0;

const titles = {
    success: 'Success',
    error: 'Something went wrong',
    info: 'Notice',
};

function dismiss(id) {
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
}

function show(message, type = 'success', title = null) {
    const id = ++nextId;

    toasts.value.push({
        id,
        message,
        type,
        title: title ?? titles[type] ?? 'Notice',
    });

    setTimeout(() => dismiss(id), 5000);

    return id;
}

export function useToast() {
    return {
        toasts,
        dismiss,
        show,
        success: (message, title = null) => show(message, 'success', title),
        error: (message, title = null) => show(message, 'error', title),
        info: (message, title = null) => show(message, 'info', title),
    };
}
