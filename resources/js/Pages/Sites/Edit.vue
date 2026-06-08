<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    site: Object,
    trackingSnippet: String,
});

const showResetModal = ref(false);
const showDeleteModal = ref(false);

const form = useForm({
    name: props.site.name,
    is_paused: props.site.is_paused,
});

const submit = () => form.patch(route('sites.update', props.site.id));

const regenerate = () => {
    if (confirm('Regenerate tracking ID? You must update your embed snippet.')) {
        router.post(route('sites.regenerate-tracking-id', props.site.id));
    }
};

const resetSite = () => {
    router.post(route('sites.reset', props.site.id), {}, {
        onSuccess: () => { showResetModal.value = false; },
    });
};

const deleteSite = () => {
    router.delete(route('sites.destroy', props.site.id));
};

const copy = (text) => navigator.clipboard.writeText(text);

const copiedSnippet = ref(false);
let copiedSnippetTimeout = null;

const copySnippet = async () => {
    await navigator.clipboard.writeText(props.trackingSnippet);
    copiedSnippet.value = true;
    if (copiedSnippetTimeout) clearTimeout(copiedSnippetTimeout);
    copiedSnippetTimeout = setTimeout(() => {
        copiedSnippet.value = false;
        copiedSnippetTimeout = null;
    }, 2000);
};

onUnmounted(() => {
    if (copiedSnippetTimeout) clearTimeout(copiedSnippetTimeout);
});

const snippetRef = ref(null);

const fitSnippetHeight = () => {
    const el = snippetRef.value;
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = `${el.scrollHeight}px`;
};

onMounted(fitSnippetHeight);
watch(() => props.trackingSnippet, fitSnippetHeight);
</script>

<template>
    <Head :title="`${site.name} — Edit`" />

    <AppLayout :site="site">
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Edit website</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ site.domain }}</p>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">
            <form class="vm-card space-y-5" @submit.prevent="submit">
                <h3 class="vm-panel-title">General</h3>

                <div>
                    <InputLabel value="Website ID" />
                    <div class="mt-1 flex items-center gap-2">
                        <code class="flex-1 rounded-lg bg-slate-100 px-3 py-2 font-mono text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ site.tracking_id }}</code>
                        <button type="button" class="vm-btn-secondary" @click="copy(site.tracking_id)">Copy</button>
                    </div>
                </div>

                <div>
                    <InputLabel for="name" value="Name" />
                    <TextInput id="name" v-model="form.name" class="vm-input mt-1" required />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel value="Domain" />
                    <TextInput :model-value="site.domain" class="vm-input mt-1" disabled />
                </div>

                <button type="submit" class="vm-btn-primary" :disabled="form.processing">Save</button>
            </form>

            <div v-if="trackingSnippet" class="vm-card space-y-3">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Tracking code</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    To track stats for this website, place the following code in the
                    <code class="text-slate-700 dark:text-slate-300">&lt;head&gt;...&lt;/head&gt;</code>
                    section of your HTML.
                </p>
                <div class="vm-code-block">
                    <button
                        type="button"
                        class="vm-code-copy"
                        :title="copiedSnippet ? 'Copied!' : 'Copy'"
                        @click="copySnippet"
                    >
                        <svg v-if="copiedSnippet" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                    <textarea
                        ref="snippetRef"
                        readonly
                        :value="trackingSnippet"
                        rows="3"
                        class="vm-code-textarea"
                        @focus="$event.target.select()"
                    />
                </div>
                <button
                    type="button"
                    class="text-sm text-slate-500 transition hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                    @click="regenerate"
                >
                    Regenerate tracking ID
                </button>
            </div>

            <div v-else class="vm-card">
                <h3 class="vm-panel-title mb-2">Tracking code</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">Tracking is paused. Resume tracking below to get your embed snippet.</p>
            </div>

            <form class="vm-card" @submit.prevent="submit">
                <h3 class="vm-panel-title mb-4">Tracking</h3>
                <label class="flex items-center gap-3">
                    <input v-model="form.is_paused" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800" />
                    <span class="text-sm text-slate-700 dark:text-slate-300">Pause tracking (no data will be collected)</span>
                </label>
                <button type="submit" class="vm-btn-primary mt-4" :disabled="form.processing">Save</button>
            </form>

            <div class="vm-card space-y-4 border-rose-200 dark:border-rose-900">
                <h3 class="vm-panel-title text-rose-600 dark:text-rose-400">Danger zone</h3>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-medium text-slate-900 dark:text-white">Reset website</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">All statistics will be deleted, but your settings will remain intact.</p>
                    </div>
                    <button type="button" class="vm-btn-secondary shrink-0" @click="showResetModal = true">Reset</button>
                </div>

                <hr class="border-slate-200 dark:border-slate-700" />

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-medium text-slate-900 dark:text-white">Delete website</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">All website data will be deleted.</p>
                    </div>
                    <button type="button" class="vm-btn-danger shrink-0" @click="showDeleteModal = true">Delete</button>
                </div>
            </div>

            <Link :href="route('sites.show', site.id)" class="inline-block text-sm text-indigo-600 dark:text-indigo-400">← Back to overview</Link>
        </div>

        <div v-if="showResetModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Reset website?</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">All page views and statistics will be permanently deleted. Site settings and tracking ID will remain.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="vm-btn-secondary" @click="showResetModal = false">Cancel</button>
                    <button type="button" class="vm-btn-primary bg-amber-600 hover:bg-amber-500" @click="resetSite">Reset statistics</button>
                </div>
            </div>
        </div>

        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Delete website?</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">This will permanently delete the site and all associated data. This action cannot be undone.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="vm-btn-secondary" @click="showDeleteModal = false">Cancel</button>
                    <button type="button" class="vm-btn-danger" @click="deleteSite">Delete website</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
