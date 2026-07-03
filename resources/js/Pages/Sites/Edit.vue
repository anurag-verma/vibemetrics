<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import PageHeader from '@/Components/PageHeader.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    site: Object,
    trackingSnippet: String,
    goals: { type: Array, default: () => [] },
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
    router.delete(route('sites.destroy', props.site.id), {
        onFinish: () => { showDeleteModal.value = false; },
    });
};

const copy = (text) => navigator.clipboard.writeText(text);

const showGoalModal = ref(false);
const editingGoal = ref(null);
const goalForm = useForm({ name: '', match_type: 'exact', url_pattern: '' });

const openNewGoal = () => {
    editingGoal.value = null;
    goalForm.reset();
    goalForm.match_type = 'exact';
    showGoalModal.value = true;
};

const openEditGoal = (goal) => {
    editingGoal.value = goal;
    goalForm.name = goal.name;
    goalForm.match_type = goal.match_type;
    goalForm.url_pattern = goal.url_pattern;
    showGoalModal.value = true;
};

const submitGoal = () => {
    if (editingGoal.value) {
        goalForm.patch(route('sites.goals.update', { site: props.site.id, goal: editingGoal.value.id }), {
            onSuccess: () => { showGoalModal.value = false; goalForm.reset(); },
        });
    } else {
        goalForm.post(route('sites.goals.store', props.site.id), {
            onSuccess: () => { showGoalModal.value = false; goalForm.reset(); },
        });
    }
};

const deleteGoal = (goal) => {
    if (confirm(`Delete goal "${goal.name}"?`)) {
        router.delete(route('sites.goals.destroy', { site: props.site.id, goal: goal.id }));
    }
};

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
        <div class="mx-auto max-w-3xl space-y-6">
            <PageHeader title="Edit website" :description="site.domain" />
            <form class="vm-card space-y-5" @submit.prevent="submit">
                <h3 class="vm-panel-title">General</h3>

                <div>
                    <InputLabel value="Website ID" />
                    <div class="mt-1 flex flex-col gap-2 sm:flex-row sm:items-center">
                        <code class="flex-1 break-all rounded-lg bg-slate-100 px-3 py-2 font-mono text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ site.tracking_id }}</code>
                        <button type="button" class="vm-btn-secondary shrink-0" @click="copy(site.tracking_id)">Copy</button>
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
                    To track stats for this website, place the following code before the closing
                    <code class="text-slate-700 dark:text-slate-300">&lt;/body&gt;</code>
                    tag on every page.
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

            <div class="vm-card space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="vm-panel-title">Goals</h3>
                    <button type="button" class="vm-btn-secondary text-sm" @click="openNewGoal">Add goal</button>
                </div>

                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Track URL visits as conversions. Use <strong>exact</strong> to match a specific URL or <strong>contains</strong> to match any URL containing the pattern.
                </p>

                <div v-if="goals.length > 0" class="divide-y divide-slate-100 dark:divide-slate-800">
                    <div
                        v-for="goal in goals"
                        :key="goal.id"
                        class="flex items-center justify-between gap-4 py-3"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-medium text-slate-800 dark:text-slate-200">{{ goal.name }}</p>
                            <p class="mt-0.5 truncate font-mono text-xs text-slate-500 dark:text-slate-400">
                                <span class="mr-1 rounded bg-slate-100 px-1 py-0.5 text-slate-600 dark:bg-slate-700 dark:text-slate-300">{{ goal.match_type }}</span>
                                {{ goal.url_pattern }}
                            </p>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <button type="button" class="vm-btn-secondary text-xs" @click="openEditGoal(goal)">Edit</button>
                            <button type="button" class="vm-btn-danger text-xs" @click="deleteGoal(goal)">Delete</button>
                        </div>
                    </div>
                </div>

                <p v-else class="text-sm italic text-slate-400 dark:text-slate-500">No goals yet. Add your first goal above.</p>
            </div>

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

        <Modal :show="showResetModal" max-width="md" @close="showResetModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Reset website?</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">All page views and statistics will be permanently deleted. Site settings and tracking ID will remain.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="vm-btn-secondary" @click="showResetModal = false">Cancel</button>
                    <button type="button" class="vm-btn-primary bg-amber-600 hover:bg-amber-500" @click="resetSite">Reset statistics</button>
                </div>
            </div>
        </Modal>

        <Modal :show="showDeleteModal" max-width="md" @close="showDeleteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Delete website?</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">This will permanently delete the site and all associated data. This action cannot be undone.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="vm-btn-secondary" @click="showDeleteModal = false">Cancel</button>
                    <button type="button" class="vm-btn-danger" @click="deleteSite">Delete website</button>
                </div>
            </div>
        </Modal>

        <Modal :show="showGoalModal" max-width="md" @close="showGoalModal = false">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900 dark:text-white">
                    {{ editingGoal ? 'Edit goal' : 'New goal' }}
                </h3>
                <form class="space-y-4" @submit.prevent="submitGoal">
                    <div>
                        <InputLabel for="goal-name" value="Goal name" />
                        <TextInput
                            id="goal-name"
                            v-model="goalForm.name"
                            class="vm-input mt-1"
                            placeholder="e.g. Signed up"
                            required
                        />
                        <InputError class="mt-1" :message="goalForm.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="goal-match-type" value="Match type" />
                        <select
                            id="goal-match-type"
                            v-model="goalForm.match_type"
                            class="vm-input mt-1"
                        >
                            <option value="exact">Exact — URL equals pattern</option>
                            <option value="contains">Contains — URL includes pattern</option>
                        </select>
                        <InputError class="mt-1" :message="goalForm.errors.match_type" />
                    </div>

                    <div>
                        <InputLabel for="goal-url" value="URL pattern" />
                        <TextInput
                            id="goal-url"
                            v-model="goalForm.url_pattern"
                            class="vm-input mt-1"
                            placeholder="e.g. /thank-you or /signup/complete"
                            required
                        />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            <span v-if="goalForm.match_type === 'exact'">Matches pages where the URL path equals this value exactly.</span>
                            <span v-else>Matches pages where the URL contains this string anywhere.</span>
                        </p>
                        <InputError class="mt-1" :message="goalForm.errors.url_pattern" />
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" class="vm-btn-secondary" @click="showGoalModal = false">Cancel</button>
                        <button type="submit" class="vm-btn-primary" :disabled="goalForm.processing">
                            {{ editingGoal ? 'Save changes' : 'Create goal' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
