<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage();
const dismissed = ref(false);

const announcement = computed(() => page.props.announcement ?? null);

const storageKey = computed(() => (
    announcement.value ? `vm-announcement-dismissed:${announcement.value.id}` : null
));

const isVisible = computed(() => {
    if (!announcement.value || dismissed.value) {
        return false;
    }

    if (!announcement.value.dismissible || !storageKey.value) {
        return true;
    }

    try {
        return localStorage.getItem(storageKey.value) !== '1';
    } catch {
        return true;
    }
});

const styles = computed(() => {
    const map = {
        info: {
            wrap: 'border-indigo-200 bg-indigo-50 text-indigo-900 dark:border-indigo-900/60 dark:bg-indigo-950/50 dark:text-indigo-100',
            link: 'text-indigo-700 hover:text-indigo-900 dark:text-indigo-200 dark:hover:text-white',
        },
        warning: {
            wrap: 'border-amber-200 bg-amber-50 text-amber-950 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-100',
            link: 'text-amber-800 hover:text-amber-950 dark:text-amber-200 dark:hover:text-white',
        },
        success: {
            wrap: 'border-emerald-200 bg-emerald-50 text-emerald-950 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-100',
            link: 'text-emerald-800 hover:text-emerald-950 dark:text-emerald-200 dark:hover:text-white',
        },
    };

    return map[announcement.value?.type] ?? map.info;
});

watch(
    () => announcement.value?.id,
    () => {
        dismissed.value = false;
    },
);

const dismiss = () => {
    if (!announcement.value?.dismissible || !storageKey.value) {
        return;
    }

    try {
        localStorage.setItem(storageKey.value, '1');
    } catch {
        // Ignore storage errors (private browsing, etc.).
    }

    dismissed.value = true;
};
</script>

<template>
    <div
        v-if="isVisible"
        class="shrink-0 border-b px-4 py-2.5"
        :class="styles.wrap"
        role="status"
        aria-live="polite"
    >
        <div class="mx-auto flex max-w-7xl items-start justify-between gap-3">
            <div class="announcement-banner__message min-w-0 flex-1 text-sm leading-relaxed">
                <div v-html="announcement.message" />
                <a
                    v-if="announcement.linkUrl"
                    :href="announcement.linkUrl"
                    class="ml-2 inline-flex font-semibold underline underline-offset-2"
                    :class="styles.link"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    {{ announcement.linkLabel }}
                </a>
            </div>
            <button
                v-if="announcement.dismissible"
                type="button"
                class="shrink-0 rounded-md p-1 opacity-70 transition hover:opacity-100"
                aria-label="Dismiss announcement"
                @click="dismiss"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</template>

<style scoped>
.announcement-banner__message :deep(strong),
.announcement-banner__message :deep(b) {
    font-weight: 700;
}

.announcement-banner__message :deep(em),
.announcement-banner__message :deep(i) {
    font-style: italic;
}

.announcement-banner__message :deep(u) {
    text-decoration: underline;
}

.announcement-banner__message :deep(p) {
    display: inline;
}

.announcement-banner__message :deep(p + p)::before {
    content: ' ';
}
</style>
