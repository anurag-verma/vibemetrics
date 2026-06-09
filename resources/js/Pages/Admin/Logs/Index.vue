<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { formatDisplayDateTime } from '@/utils/date';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    files: { type: Array, default: () => [] },
    levels: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    log: { type: Object, default: () => ({}) },
});

const refreshing = ref(false);
const form = ref({
    file: props.filters.file ?? '',
    level: props.filters.level ?? '',
    search: props.filters.search ?? '',
    lines: props.filters.lines ?? 500,
});

watch(
    () => props.filters,
    (value) => {
        form.value = {
            file: value.file ?? '',
            level: value.level ?? '',
            search: value.search ?? '',
            lines: value.lines ?? 500,
        };
    },
    { deep: true },
);

const hasFiles = computed(() => props.files.length > 0);
const entryCount = computed(() => props.log.entries?.length ?? 0);

const formatBytes = (bytes) => {
    if (bytes == null) return '—';
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    if (bytes < 1024 * 1024 * 1024) return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
    return `${(bytes / 1024 / 1024 / 1024).toFixed(2)} GB`;
};

const formatTimestamp = (value) => {
    if (!value) return '—';
    return formatDisplayDateTime(value) || '—';
};

const levelClasses = (level) => ({
    EMERGENCY: 'text-rose-300',
    ALERT: 'text-rose-300',
    CRITICAL: 'text-rose-300',
    ERROR: 'text-rose-300',
    WARNING: 'text-amber-300',
    NOTICE: 'text-sky-300',
    INFO: 'text-emerald-300',
    DEBUG: 'text-slate-400',
}[level] ?? 'text-slate-200');

const levelBadgeClasses = (level) => ({
    EMERGENCY: 'bg-rose-950 text-rose-200 ring-rose-900',
    ALERT: 'bg-rose-950 text-rose-200 ring-rose-900',
    CRITICAL: 'bg-rose-950 text-rose-200 ring-rose-900',
    ERROR: 'bg-rose-950 text-rose-200 ring-rose-900',
    WARNING: 'bg-amber-950 text-amber-200 ring-amber-900',
    NOTICE: 'bg-sky-950 text-sky-200 ring-sky-900',
    INFO: 'bg-emerald-950 text-emerald-200 ring-emerald-900',
    DEBUG: 'bg-slate-800 text-slate-300 ring-slate-700',
}[level] ?? 'bg-slate-800 text-slate-300 ring-slate-700');

const queryParams = () => {
    const params = {
        lines: Number(form.value.lines) || 500,
    };

    if (form.value.file) params.file = form.value.file;
    if (form.value.level) params.level = form.value.level;
    if (form.value.search?.trim()) params.search = form.value.search.trim();

    return params;
};

const applyFilters = () => {
    if (refreshing.value) return;
    refreshing.value = true;

    router.get(route('admin.logs.index'), queryParams(), {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            refreshing.value = false;
        },
    });
};

const refresh = () => applyFilters();
</script>

<template>
    <Head title="Logs" />

    <AdminLayout>
        <PageHeader title="Application logs">
            <template #description>
                <p class="vm-page-description">
                    Laravel log files from <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800">storage/logs</code>
                </p>
            </template>
            <template #actions>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    :disabled="refreshing || !hasFiles"
                    @click="refresh"
                >
                    <svg
                        class="h-4 w-4"
                        :class="{ 'animate-spin': refreshing }"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </template>
        </PageHeader>

        <div class="space-y-4">
            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <form class="grid gap-4 lg:grid-cols-4" @submit.prevent="applyFilters">
                    <div>
                        <label for="log-file" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Log file</label>
                        <select
                            id="log-file"
                            v-model="form.file"
                            class="vm-input w-full"
                            :disabled="!hasFiles"
                        >
                            <option v-if="!hasFiles" value="">No log files found</option>
                            <option v-for="file in files" :key="file.name" :value="file.name">
                                {{ file.name }} ({{ formatBytes(file.size_bytes) }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="log-level" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Level</label>
                        <select id="log-level" v-model="form.level" class="vm-input w-full" :disabled="!hasFiles">
                            <option value="">All levels</option>
                            <option v-for="level in levels" :key="level" :value="level">{{ level }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="log-search" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Search</label>
                        <input
                            id="log-search"
                            v-model="form.search"
                            type="search"
                            class="vm-input w-full"
                            placeholder="Filter message text"
                            :disabled="!hasFiles"
                        >
                    </div>

                    <div class="flex items-end gap-2">
                        <div class="min-w-0 flex-1">
                            <label for="log-lines" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Entries</label>
                            <select id="log-lines" v-model="form.lines" class="vm-input w-full" :disabled="!hasFiles">
                                <option :value="100">Last 100</option>
                                <option :value="250">Last 250</option>
                                <option :value="500">Last 500</option>
                                <option :value="1000">Last 1000</option>
                                <option :value="2000">Last 2000</option>
                            </select>
                        </div>
                        <button
                            type="submit"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-500 disabled:opacity-60"
                            :disabled="!hasFiles || refreshing"
                        >
                            Apply
                        </button>
                    </div>
                </form>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                    <div class="text-sm text-slate-600 dark:text-slate-300">
                        <span v-if="log.file" class="font-medium text-slate-900 dark:text-white">{{ log.file }}</span>
                        <span v-else class="text-slate-500 dark:text-slate-400">No log file selected</span>
                        <span v-if="log.file" class="text-slate-400 dark:text-slate-500"> · {{ formatBytes(log.size_bytes) }}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <span v-if="log.modified_at">Updated {{ formatTimestamp(log.modified_at) }}</span>
                        <span v-if="entryCount">{{ entryCount }} entries</span>
                        <span
                            v-if="log.truncated"
                            class="rounded-full bg-amber-100 px-2 py-0.5 text-amber-800 dark:bg-amber-950 dark:text-amber-200"
                        >
                            Showing tail only
                        </span>
                    </div>
                </div>

                <div v-if="!hasFiles" class="px-4 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                    No Laravel log files were found in <code>storage/logs</code>.
                </div>

                <div v-else-if="!log.content" class="px-4 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                    No log entries match the current filters.
                </div>

                <div v-else class="max-h-[calc(100vh-20rem)] overflow-auto bg-slate-950 p-4 font-mono text-xs leading-6 text-slate-200">
                    <div
                        v-for="(entry, index) in log.entries"
                        :key="`${entry.timestamp}-${index}`"
                        class="border-b border-slate-800/80 py-3 last:border-b-0"
                    >
                        <div class="mb-1 flex flex-wrap items-center gap-2">
                            <span
                                v-if="entry.level"
                                class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 ring-inset"
                                :class="levelBadgeClasses(entry.level)"
                            >
                                {{ entry.level }}
                            </span>
                            <span v-if="entry.timestamp" class="text-slate-500">{{ entry.timestamp }}</span>
                            <span v-if="entry.environment" class="text-slate-600">{{ entry.environment }}</span>
                        </div>
                        <pre class="whitespace-pre-wrap break-words" :class="levelClasses(entry.level)">{{ entry.message }}</pre>
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
