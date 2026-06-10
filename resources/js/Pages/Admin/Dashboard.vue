<script setup>
import AdminDateRangePicker from '@/Components/AdminDateRangePicker.vue';
import PageHeader from '@/Components/PageHeader.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MetricCard from '@/Components/MetricCard.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import { Bar } from 'vue-chartjs';
import { useRelativeUpdatedLabel } from '@/Composables/useRelativeUpdatedLabel';
import { formatTrendLabel } from '@/utils/date';
import { computed, onMounted, onUnmounted, ref } from 'vue';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const props = defineProps({
    dateRange: Object,
    kpis: Object,
    system: Object,
    registrationTrend: Array,
    trafficTrend: Array,
    ingestionRate: Array,
    topSites: Array,
    recentActivity: Object,
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

const trendGranularity = computed(() => props.dateRange?.granularity ?? 'day');

const trendLabel = (value) => formatTrendLabel(value, trendGranularity.value);

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { display: false },
        tooltip: { padding: 12, cornerRadius: 8 },
    },
    scales: {
        x: { grid: { display: false }, ticks: { maxTicksLimit: 12 } },
        y: { beginAtZero: true, ticks: { precision: 0 } },
    },
};

const regChart = computed(() => ({
    labels: props.registrationTrend.map((d) => trendLabel(d.date)),
    datasets: [{
        label: 'New users',
        data: props.registrationTrend.map((d) => d.count),
        backgroundColor: 'rgba(99, 102, 241, 0.85)',
        borderRadius: 6,
    }],
}));

const trafficChart = computed(() => ({
    labels: props.trafficTrend.map((d) => trendLabel(d.date)),
    datasets: [{
        label: 'Page views',
        data: props.trafficTrend.map((d) => d.count),
        backgroundColor: 'rgba(16, 185, 129, 0.85)',
        borderRadius: 6,
    }],
}));

const ingestChart = computed(() => ({
    labels: props.ingestionRate.map((d) => formatTrendLabel(d.hour, 'hour')),
    datasets: [{
        label: 'Events',
        data: props.ingestionRate.map((d) => d.count),
        backgroundColor: 'rgba(129, 140, 248, 0.85)',
        borderRadius: 6,
    }],
}));

const hasRegistrations = computed(() => props.registrationTrend.some((d) => d.count > 0));
const hasTraffic = computed(() => props.trafficTrend.some((d) => d.count > 0));
const hasIngestion = computed(() => props.ingestionRate.some((d) => d.count > 0));
</script>

<template>
    <Head title="Admin Overview" />

    <AdminLayout>
        <div class="mx-auto max-w-7xl space-y-6">
            <PageHeader
                title="Platform overview"
                description="System health, growth, and ingestion."
            >
                <template #actions>
                    <span class="hidden text-xs text-slate-500 dark:text-slate-400 sm:inline">{{ lastUpdatedLabel }}</span>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-600 transition hover:bg-slate-50 disabled:opacity-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                        title="Refresh data"
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
                    <AdminDateRangePicker :date-range="dateRange" />
                </template>
            </PageHeader>
            <div class="flex flex-wrap gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium"
                    :class="system.maintenance_mode
                        ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200'
                        : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'"
                >
                    <span class="h-1.5 w-1.5 rounded-full" :class="system.maintenance_mode ? 'bg-amber-500' : 'bg-emerald-500'" />
                    {{ system.maintenance_mode ? 'Maintenance on' : 'Maintenance off' }}
                </span>
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium"
                    :class="system.registration_enabled
                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'
                        : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-200'"
                >
                    Registration {{ system.registration_enabled ? 'open' : 'closed' }}
                </span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                    Rollup {{ system.rollup_enabled ? 'on' : 'off' }}
                </span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                    Retention {{ system.retention_days }}d
                </span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                    Rate limit {{ system.collect_rate_limit }}/min
                </span>
                <Link
                    :href="route('admin.settings.index')"
                    class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 transition hover:bg-indigo-100 dark:bg-indigo-950 dark:text-indigo-300 dark:hover:bg-indigo-900"
                >
                    Edit settings →
                </Link>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <MetricCard title="Total users" :value="kpis.total_users.toLocaleString()" :subtitle="`+${kpis.new_users_7d} this week`" icon="users" accent="indigo" />
                <MetricCard title="Tracking sites" :value="kpis.total_sites.toLocaleString()" :subtitle="`${kpis.paused_sites} paused · +${kpis.new_sites_7d} this week`" icon="globe" accent="emerald" />
                <MetricCard title="Total events" :value="kpis.total_page_views.toLocaleString()" :subtitle="`${kpis.live_events} live now`" icon="chart" accent="amber" />
                <MetricCard
                    title="Events today"
                    :value="kpis.page_views_today.toLocaleString()"
                    :subtitle="`${kpis.db_growth >= 0 ? '+' : ''}${kpis.db_growth}% vs yesterday`"
                    icon="chart"
                    accent="rose"
                />
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="vm-card">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Disabled users</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ kpis.disabled_users }}</p>
                </div>
                <div class="vm-card">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Unverified users</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ kpis.unverified_users }}</p>
                </div>
                <div class="vm-card">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Users at site limit</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ kpis.users_at_limit }}</p>
                    <p class="mt-1 text-xs text-slate-400">Limit: {{ system.max_sites_per_user }} sites</p>
                </div>
                <div class="vm-card">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Paused sites</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ kpis.paused_sites }}</p>
                    <Link :href="route('admin.sites.index')" class="mt-1 inline-block text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                        Manage sites →
                    </Link>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="vm-card">
                    <h3 class="vm-panel-title mb-4">Registrations ({{ dateRange.label }})</h3>
                    <div v-if="hasRegistrations" class="h-56">
                        <Bar :data="regChart" :options="chartOptions" />
                    </div>
                    <p v-else class="flex h-56 items-center justify-center text-sm text-slate-400">No signups in this period</p>
                </div>
                <div class="vm-card">
                    <h3 class="vm-panel-title mb-4">Platform traffic ({{ dateRange.label }})</h3>
                    <div v-if="hasTraffic" class="h-56">
                        <Bar :data="trafficChart" :options="chartOptions" />
                    </div>
                    <p v-else class="flex h-56 items-center justify-center text-sm text-slate-400">No events recorded yet</p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="vm-card lg:col-span-2">
                    <h3 class="vm-panel-title mb-4">Ingestion rate (24h)</h3>
                    <div v-if="hasIngestion" class="h-56">
                        <Bar :data="ingestChart" :options="chartOptions" />
                    </div>
                    <p v-else class="flex h-56 items-center justify-center text-sm text-slate-400">No events in the last 24 hours</p>
                </div>

                <div class="vm-card">
                    <h3 class="vm-panel-title mb-4">Recent activity</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">New users</p>
                            <ul v-if="recentActivity.users.length" class="space-y-2">
                                <li v-for="user in recentActivity.users" :key="user.id" class="text-sm">
                                    <p class="font-medium text-slate-800 dark:text-slate-200">{{ user.name }}</p>
                                    <p class="text-xs text-slate-500">{{ user.email }} · {{ user.created_at }}</p>
                                </li>
                            </ul>
                            <p v-else class="text-sm text-slate-400">No users yet</p>
                        </div>
                        <div class="border-t border-slate-100 pt-4 dark:border-slate-800">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">New sites</p>
                            <ul v-if="recentActivity.sites.length" class="space-y-2">
                                <li v-for="site in recentActivity.sites" :key="site.id" class="text-sm">
                                    <p class="font-medium text-slate-800 dark:text-slate-200">{{ site.name }}</p>
                                    <p class="text-xs text-slate-500">{{ site.domain }} · {{ site.created_at }}</p>
                                </li>
                            </ul>
                            <p v-else class="text-sm text-slate-400">No sites yet</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="vm-card overflow-hidden">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="vm-panel-title">Top sites by volume ({{ dateRange.label }})</h3>
                    <Link :href="route('admin.sites.index')" class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                        View all sites →
                    </Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                <th class="pb-3 font-medium">Site</th>
                                <th class="pb-3 font-medium">Domain</th>
                                <th class="pb-3 font-medium">Owner</th>
                                <th class="pb-3 font-medium">Status</th>
                                <th class="pb-3 text-right font-medium">Share</th>
                                <th class="pb-3 text-right font-medium">Events</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="site in topSites" :key="site.id" class="text-slate-700 dark:text-slate-300">
                                <td class="py-3 font-medium">{{ site.name }}</td>
                                <td class="py-3">
                                    <a
                                        :href="`https://${site.domain}`"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-indigo-600 hover:underline dark:text-indigo-400"
                                    >
                                        {{ site.domain }}
                                    </a>
                                </td>
                                <td class="py-3 text-slate-500 dark:text-slate-400">{{ site.owner_email }}</td>
                                <td class="py-3">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="site.is_paused
                                            ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200'
                                            : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'"
                                    >
                                        {{ site.is_paused ? 'Paused' : 'Active' }}
                                    </span>
                                </td>
                                <td class="py-3 text-right text-slate-500">{{ site.share }}%</td>
                                <td class="py-3 text-right font-semibold">{{ site.page_views.toLocaleString() }}</td>
                            </tr>
                            <tr v-if="!topSites.length">
                                <td colspan="6" class="py-8 text-center text-slate-400">No events recorded yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
