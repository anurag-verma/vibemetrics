<script setup>
import { useToast } from '@/Composables/useToast';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

const page = usePage();
const { toasts, dismiss, success, error: showError } = useToast();

const styles = {
    success: {
        wrap: 'bg-emerald-50 text-emerald-900 ring-emerald-200 dark:bg-emerald-950/90 dark:text-emerald-100 dark:ring-emerald-800',
        icon: 'text-emerald-600 dark:text-emerald-400',
    },
    error: {
        wrap: 'bg-rose-50 text-rose-900 ring-rose-200 dark:bg-rose-950/90 dark:text-rose-100 dark:ring-rose-800',
        icon: 'text-rose-600 dark:text-rose-400',
    },
    info: {
        wrap: 'bg-sky-50 text-sky-900 ring-sky-200 dark:bg-sky-950/90 dark:text-sky-100 dark:ring-sky-800',
        icon: 'text-sky-600 dark:text-sky-400',
    },
};

watch(
    () => [page.props.flash?.success, page.props.flash?.error],
    ([flashSuccess, flashError]) => {
        if (flashSuccess) {
            success(flashSuccess);
        }

        if (flashError) {
            showError(flashError);
        }
    },
    { immediate: true },
);
</script>

<template>
    <div class="pointer-events-none fixed bottom-6 right-6 z-[100] flex w-full max-w-sm flex-col gap-3">
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="translate-y-1 opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto flex items-start gap-3 rounded-xl px-4 py-3 shadow-lg ring-1"
                :class="styles[toast.type]?.wrap"
                role="status"
            >
                <div class="mt-0.5 shrink-0" :class="styles[toast.type]?.icon">
                    <svg
                        v-if="toast.type === 'success'"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg
                        v-else-if="toast.type === 'error'"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg
                        v-else
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold">{{ toast.title }}</p>
                    <p class="mt-0.5 text-sm opacity-90">{{ toast.message }}</p>
                </div>

                <button
                    type="button"
                    class="shrink-0 rounded-md p-1 opacity-60 transition hover:opacity-100"
                    aria-label="Dismiss"
                    @click="dismiss(toast.id)"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
