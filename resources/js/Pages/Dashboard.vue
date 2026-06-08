<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CountryMap from '@/Components/CountryMap.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import MetricCard from '@/Components/MetricCard.vue';
import RankList from '@/Components/RankList.vue';
import TabbedPanel from '@/Components/TabbedPanel.vue';
import TrafficHeatmap from '@/Components/TrafficHeatmap.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Filler,
    Legend,
} from 'chart.js';
import { Line } from 'vue-chartjs';
import { useRelativeUpdatedLabel } from '@/Composables/useRelativeUpdatedLabel';
import { formatDisplayDate } from '@/utils/date';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Filler, Legend);

const props = defineProps({
    site: Object,
    metrics: Object,
    range: Number,
});

const refreshing = ref(false);
const lastUpdated = ref(new Date());
const liveVisitors = ref(props.metrics.live_visitors);
let metricsRefreshInterval = null;

const { lastUpdatedLabel } = useRelativeUpdatedLabel(lastUpdated);

const hasData = computed(() => props.metrics.total_page_views > 0);

const formatChange = (pct) => {
    const sign = pct >= 0 ? '+' : '';
    return `${sign}${pct}%`;
};

const periodChangeLabel = (pct) => `${formatChange(pct)} vs prior ${props.range}d`;

const viewsSubtitle = computed(() => periodChangeLabel(props.metrics.views_period_change_pct));

const visitorsSubtitle = computed(() => {
    const change = periodChangeLabel(props.metrics.visitors_period_change_pct);
    return `${change} · Privacy-safe estimate`;
});

const todaySubtitle = computed(() => `${formatChange(props.metrics.views_today_change_pct)} vs yesterday`);

const pagesPerVisitorSubtitle = computed(() => `${props.metrics.avg_views_per_day} avg views/day`);

const pagesTabs = computed(() => [
    { id: 'paths', label: 'Path', items: props.metrics.top_urls, labelType: 'path' },
    { id: 'urls', label: 'URL', items: props.metrics.top_urls, labelType: 'default' },
]);

const sourcesTabs = computed(() => [
    { id: 'referrers', label: 'Referrers', items: props.metrics.top_referrers, labelType: 'referrer' },
    { id: 'channels', label: 'Channels', items: props.metrics.channels },
    { id: 'campaigns', label: 'Campaigns', items: props.metrics.top_campaigns },
    { id: 'utm_source', label: 'UTM source', items: props.metrics.utm_sources },
    { id: 'utm_medium', label: 'UTM medium', items: props.metrics.utm_mediums },
]);

const environmentTabs = computed(() => [
    { id: 'browsers', label: 'Browsers', items: props.metrics.browsers },
    { id: 'os', label: 'OS', items: props.metrics.operating_systems },
    { id: 'devices', label: 'Devices', items: props.metrics.devices },
]);

const refresh = (silent = false) => {
    if (refreshing.value) return;

    if (!silent) refreshing.value = true;

    router.reload({
        only: ['metrics'],
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            refreshing.value = false;
            lastUpdated.value = new Date();
            liveVisitors.value = props.metrics.live_visitors;
        },
    });
};

watch(() => props.metrics.live_visitors, (value) => {
    liveVisitors.value = value;
});

onMounted(() => {
    metricsRefreshInterval = setInterval(() => {
        if (document.visibilityState === 'visible') {
            refresh(true);
        }
    }, 60000);
});

onUnmounted(() => {
    clearInterval(metricsRefreshInterval);
});

const chartLabels = computed(() => props.metrics.daily_trend.map((d) => formatDisplayDate(d.date)));

const chartData = computed(() => ({
    labels: chartLabels.value,
    datasets: [
        {
            label: 'Views',
            data: props.metrics.daily_trend.map((d) => d.count),
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.08)',
            fill: true,
            tension: 0.35,
            pointRadius: 0,
            pointHoverRadius: 5,
            borderWidth: 2,
        },
        {
            label: 'Visitors',
            data: props.metrics.visitors_trend.map((d) => d.count),
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.06)',
            fill: true,
            tension: 0.35,
            pointRadius: 0,
            pointHoverRadius: 5,
            borderWidth: 2,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: {
            display: true,
            position: 'top',
            align: 'end',
            labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, pointStyle: 'circle' },
        },
        tooltip: { padding: 12, cornerRadius: 8 },
    },
    scales: {
        x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } },
        y: { beginAtZero: true, ticks: { precision: 0 } },
    },
};
</script>

<template>
    <Head :title="`${site.name} — Overview`" />

    <AppLayout :site="site" :range="range" show-site-toolbar>
        <div class="mx-auto max-w-7xl space-y-6">
            <div v-if="site.is_paused" class="rounded-xl border border-slate-200 bg-slate-100 p-4 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                Tracking is paused.
                <Link :href="route('sites.edit', site.id)" class="font-semibold text-indigo-600 dark:text-indigo-400">Resume tracking</Link>
            </div>

            <div
                v-else-if="!hasData"
                class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200"
            >
                Waiting for your first pageview on <span class="font-medium">{{ site.domain }}</span>.
                <Link :href="route('sites.edit', site.id)" class="ml-1 font-semibold underline underline-offset-2">Install snippet</Link>
                <span class="mx-1 text-amber-700/60 dark:text-amber-400/60">·</span>
                <Link :href="route('documentation')" class="font-semibold underline underline-offset-2">Setup guide</Link>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-lg font-semibold text-slate-900 dark:text-white">{{ site.name }}</h1>
                        <span class="vm-badge-live">{{ liveVisitors }} live</span>
                        <span
                            v-if="!site.is_paused"
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                            :class="hasData
                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'
                                : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200'"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                :class="hasData ? 'bg-emerald-500' : 'bg-amber-500'"
                            />
                            {{ hasData ? 'Receiving data' : 'Waiting for data' }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ site.domain }} · Last {{ range }} days
                    </p>
                </div>
                <div class="flex items-center gap-2">
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
                    <DateRangePicker :model-value="range" :site-id="site.id" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                <MetricCard
                    title="Views"
                    :value="metrics.total_page_views.toLocaleString()"
                    :subtitle="viewsSubtitle"
                    icon="chart"
                    accent="indigo"
                />
                <MetricCard
                    title="Visitors"
                    :value="metrics.unique_visitors.toLocaleString()"
                    :subtitle="visitorsSubtitle"
                    icon="users"
                    accent="emerald"
                />
                <MetricCard
                    title="Views today"
                    :value="metrics.page_views_today.toLocaleString()"
                    :subtitle="todaySubtitle"
                    icon="chart"
                    accent="amber"
                />
                <MetricCard
                    title="Pages / visitor"
                    :value="metrics.pages_per_visitor.toLocaleString()"
                    :subtitle="pagesPerVisitorSubtitle"
                    icon="device"
                    accent="rose"
                />
                <MetricCard
                    title="Live now"
                    :value="liveVisitors.toLocaleString()"
                    subtitle="Active in last 5 min"
                    icon="live"
                    accent="indigo"
                />
            </div>

            <div class="vm-card">
                <h3 class="vm-panel-title mb-4">Traffic ({{ range }}d)</h3>
                <div class="h-72">
                    <Line :data="chartData" :options="chartOptions" />
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <TabbedPanel title="Pages" :tabs="pagesTabs" />
                <TabbedPanel title="Sources" :tabs="sourcesTabs" />
                <TabbedPanel title="Environment" :tabs="environmentTabs" />
                <RankList
                    title="Location"
                    :items="metrics.countries"
                    label-type="country"
                />
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <CountryMap :countries="metrics.countries" />
                <TrafficHeatmap :cells="metrics.traffic_heatmap" />
            </div>
        </div>
    </AppLayout>
</template>
