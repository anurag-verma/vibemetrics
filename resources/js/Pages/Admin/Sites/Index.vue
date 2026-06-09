<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
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
        <PageHeader
            title="Sites"
            description="All tracking sites across the platform."
        />

        <div class="vm-card overflow-hidden">
            <div class="space-y-4 p-4 md:hidden">
                <div
                    v-for="site in sites.data"
                    :key="`card-${site.id}`"
                    class="rounded-xl border border-slate-200 p-4 dark:border-slate-700"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 dark:text-white">{{ site.name }}</p>
                            <a
                                :href="`https://${site.domain}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-0.5 block truncate text-sm text-indigo-600 hover:underline dark:text-indigo-400"
                            >
                                {{ site.domain }}
                            </a>
                        </div>
                        <button
                            type="button"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors"
                            :class="site.is_paused ? 'bg-slate-200 dark:bg-slate-700' : 'bg-indigo-600'"
                            :title="site.is_paused ? 'Resume tracking' : 'Pause tracking'"
                            @click="togglePause(site)"
                        >
                            <span
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition"
                                :class="site.is_paused ? 'translate-x-0' : 'translate-x-5'"
                            />
                        </button>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Owner</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-300">{{ site.owner_name }}</dd>
                            <dd class="truncate text-xs text-slate-500 dark:text-slate-400">{{ site.owner_email }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Events (7d)</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-300">{{ site.events_7d.toLocaleString() }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Created</dt>
                            <dd class="mt-0.5 text-slate-600 dark:text-slate-400">{{ site.created_at }}</dd>
                        </div>
                    </dl>

                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                        Tracking {{ site.is_paused ? 'paused' : 'active' }}
                    </p>
                </div>

                <p v-if="!sites.data.length" class="py-8 text-center text-slate-400">No sites yet</p>
            </div>

            <div class="hidden overflow-x-auto md:block">
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
