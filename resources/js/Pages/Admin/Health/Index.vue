<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useRelativeUpdatedLabel } from '@/Composables/useRelativeUpdatedLabel';
import { formatDisplayDateTime } from '@/utils/date';
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    health: Object,
});

const refreshing = ref(false);
const lastUpdated = ref(new Date());
let autoRefreshInterval = null;

const { lastUpdatedLabel } = useRelativeUpdatedLabel(lastUpdated);

const refresh = (silent = false) => {
    if (refreshing.value) return;
    if (!silent) refreshing.value = true;

    router.reload({
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            refreshing.value = false;
            lastUpdated.value = new Date();
        },
    });
};

onMounted(() => {
    autoRefreshInterval = setInterval(() => {
        if (document.visibilityState === 'visible') {
            refresh(true);
        }
    }, 60000);
});

onUnmounted(() => clearInterval(autoRefreshInterval));

const statusLabel = (status) => ({
    healthy: 'Healthy',
    degraded: 'Degraded',
    unhealthy: 'Unhealthy',
}[status] ?? status);

const statusClasses = (status) => ({
    healthy: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
    degraded: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
    unhealthy: 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-200',
}[status] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300');

const dotClasses = (status) => ({
    healthy: 'bg-emerald-500',
    degraded: 'bg-amber-500',
    unhealthy: 'bg-rose-500',
}[status] ?? 'bg-slate-400');

const formatBytes = (bytes) => {
    if (bytes == null) return '—';
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    if (bytes < 1024 * 1024 * 1024) return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
    return `${(bytes / 1024 / 1024 / 1024).toFixed(2)} GB`;
};

const formatTimestamp = (value) => {
    if (!value) return 'Never';
    const formatted = formatDisplayDateTime(value);
    return formatted || 'Unknown';
};

const checkEntries = computed(() => [
    { key: 'database', title: 'Database', icon: 'database' },
    { key: 'cache', title: 'Cache', icon: 'cache' },
    { key: 'queue', title: 'Queue', icon: 'queue' },
    { key: 'storage', title: 'Storage', icon: 'storage' },
    { key: 'scheduler', title: 'Scheduler', icon: 'scheduler' },
]);
</script>

<template>
    <Head title="Admin — System Health" />

    <AdminLayout>
        <template #header>
            <div class="flex flex-1 flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-lg font-semibold text-slate-900 dark:text-white">System health</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Server resources and infrastructure status</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="hidden text-xs text-slate-500 dark:text-slate-400 sm:inline">{{ lastUpdatedLabel }}</span>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-600 transition hover:bg-slate-50 disabled:opacity-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                        title="Refresh health checks"
                        :disabled="refreshing"
                        @click="refresh()"
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
                    </button>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex flex-wrap items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium"
                    :class="statusClasses(health.status)"
                >
                    <span class="h-1.5 w-1.5 rounded-full" :class="dotClasses(health.status)" />
                    Overall: {{ statusLabel(health.status) }}
                </span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                    {{ health.app.env }}
                </span>
                <span
                    v-if="health.app.debug"
                    class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800 dark:bg-amber-950 dark:text-amber-200"
                >
                    Debug on
                </span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="vm-card">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">PHP</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ health.app.php_version }}</p>
                    <p class="mt-1 text-xs text-slate-400">Memory {{ health.app.memory_usage_mb }} MB · Peak {{ health.app.memory_peak_mb }} MB</p>
                </div>
                <div class="vm-card">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Laravel</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ health.app.laravel_version }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ health.app.timezone }}</p>
                </div>
                <div class="vm-card">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Database size</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ formatBytes(health.checks.database.size_bytes) }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ health.checks.database.driver }} · {{ health.checks.database.latency_ms ?? '—' }} ms</p>
                </div>
                <div class="vm-card">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Disk usage</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
                        {{ health.checks.storage.used_percent != null ? `${health.checks.storage.used_percent}%` : '—' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                        {{ formatBytes(health.checks.storage.free_bytes) }} free · Log {{ formatBytes(health.checks.storage.log_size_bytes) }}
                    </p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="entry in checkEntries"
                    :key="entry.key"
                    class="vm-card"
                >
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <h3 class="vm-panel-title">{{ entry.title }}</h3>
                        <span
                            class="inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="statusClasses(health.checks[entry.key].status)"
                        >
                            <span class="h-1.5 w-1.5 rounded-full" :class="dotClasses(health.checks[entry.key].status)" />
                            {{ statusLabel(health.checks[entry.key].status) }}
                        </span>
                    </div>

                    <dl class="space-y-2 text-sm">
                        <template v-if="entry.key === 'database'">
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Driver</dt>
                                <dd class="font-medium text-slate-800 dark:text-slate-200">{{ health.checks.database.driver }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Latency</dt>
                                <dd class="font-medium text-slate-800 dark:text-slate-200">{{ health.checks.database.latency_ms ?? '—' }} ms</dd>
                            </div>
                        </template>

                        <template v-else-if="entry.key === 'cache'">
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Store</dt>
                                <dd class="font-medium text-slate-800 dark:text-slate-200">{{ health.checks.cache.store }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Latency</dt>
                                <dd class="font-medium text-slate-800 dark:text-slate-200">{{ health.checks.cache.latency_ms ?? '—' }} ms</dd>
                            </div>
                        </template>

                        <template v-else-if="entry.key === 'queue'">
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Connection</dt>
                                <dd class="font-medium text-slate-800 dark:text-slate-200">{{ health.checks.queue.connection }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Pending</dt>
                                <dd class="font-medium text-slate-800 dark:text-slate-200">{{ health.checks.queue.pending_jobs ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Failed</dt>
                                <dd class="font-medium text-slate-800 dark:text-slate-200">{{ health.checks.queue.failed_jobs ?? '—' }}</dd>
                            </div>
                        </template>

                        <template v-else-if="entry.key === 'storage'">
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Used</dt>
                                <dd class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ health.checks.storage.used_percent != null ? `${health.checks.storage.used_percent}%` : '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Free</dt>
                                <dd class="font-medium text-slate-800 dark:text-slate-200">{{ formatBytes(health.checks.storage.free_bytes) }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Log file</dt>
                                <dd class="font-medium text-slate-800 dark:text-slate-200">{{ formatBytes(health.checks.storage.log_size_bytes) }}</dd>
                            </div>
                        </template>

                        <template v-else-if="entry.key === 'scheduler'">
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Rollup</dt>
                                <dd class="text-right font-medium text-slate-800 dark:text-slate-200">
                                    {{ health.checks.scheduler.rollup_schedule }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Last rollup</dt>
                                <dd class="text-right font-medium text-slate-800 dark:text-slate-200">
                                    {{ formatTimestamp(health.checks.scheduler.last_rollup_at) }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Last purge</dt>
                                <dd class="text-right font-medium text-slate-800 dark:text-slate-200">
                                    {{ formatTimestamp(health.checks.scheduler.last_purge_at) }}
                                </dd>
                            </div>
                        </template>
                    </dl>

                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">{{ health.checks[entry.key].message }}</p>
                </div>
            </div>

            <div class="vm-card overflow-hidden">
                <h3 class="vm-panel-title mb-4">Database tables</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                <th class="pb-3 font-medium">Table</th>
                                <th class="pb-3 text-right font-medium">Rows</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="table in health.tables" :key="table.table" class="text-slate-700 dark:text-slate-300">
                                <td class="py-3 font-medium">{{ table.label }}</td>
                                <td class="py-3 text-right font-semibold">
                                    {{ table.rows != null ? table.rows.toLocaleString() : '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
