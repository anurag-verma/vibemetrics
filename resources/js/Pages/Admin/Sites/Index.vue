<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({
    sites: Object,
});

const togglePause = (site) => {
    router.patch(route('admin.sites.update', site.id), {
        is_paused: !site.is_paused,
    }, { preserveScroll: true });
};
</script>

<template>
    <Head title="Admin — Sites" />

    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Sites</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">All tracking sites across the platform</p>
            </div>
        </template>

        <div class="vm-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500 dark:border-slate-700 dark:text-slate-400">
                            <th class="pb-3 pr-4 font-medium">Site</th>
                            <th class="pb-3 pr-4 font-medium">Domain</th>
                            <th class="pb-3 pr-4 font-medium">Owner</th>
                            <th class="pb-3 pr-4 font-medium">Events (7d)</th>
                            <th class="pb-3 pr-4 font-medium">Created</th>
                            <th class="pb-3 pr-4 font-medium">Tracking</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="site in sites.data" :key="site.id" class="text-slate-700 dark:text-slate-300">
                            <td class="py-3 pr-4 font-medium">{{ site.name }}</td>
                            <td class="py-3 pr-4">
                                <a
                                    :href="`https://${site.domain}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-indigo-600 hover:underline dark:text-indigo-400"
                                >
                                    {{ site.domain }}
                                </a>
                            </td>
                            <td class="py-3 pr-4">
                                <p>{{ site.owner_name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ site.owner_email }}</p>
                            </td>
                            <td class="py-3 pr-4">{{ site.events_7d.toLocaleString() }}</td>
                            <td class="py-3 pr-4 text-slate-500 dark:text-slate-400">{{ site.created_at }}</td>
                            <td class="py-3 pr-4">
                                <button
                                    type="button"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                                    :class="site.is_paused ? 'bg-slate-200 dark:bg-slate-700' : 'bg-indigo-600'"
                                    :title="site.is_paused ? 'Resume tracking' : 'Pause tracking'"
                                    @click="togglePause(site)"
                                >
                                    <span
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="site.is_paused ? 'translate-x-0' : 'translate-x-5'"
                                    />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!sites.data.length">
                            <td colspan="6" class="py-8 text-center text-slate-400">No sites yet</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="sites.links?.length > 3" class="mt-4 flex flex-wrap gap-1 border-t border-slate-100 pt-4 dark:border-slate-800">
                <template v-for="(link, index) in sites.links" :key="index">
                    <button
                        v-if="link.url"
                        type="button"
                        class="rounded-md px-3 py-1 text-xs font-medium transition"
                        :class="link.active
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800'"
                        :disabled="link.active"
                        @click="router.get(link.url, {}, { preserveScroll: true })"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </AdminLayout>
</template>
