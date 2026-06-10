<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { DATE_RANGE_PRESETS } from '@/data/dateRanges';
import { computed, ref } from 'vue';

const props = defineProps({
    settings: Object,
    branding: Object,
});

const form = useForm({
    max_sites_per_user: props.settings.max_sites_per_user,
    retention_days: props.settings.retention_days,
    rollup_enabled: props.settings.rollup_enabled,
    collect_rate_limit: props.settings.collect_rate_limit,
    registration_enabled: props.settings.registration_enabled,
    default_date_range: props.settings.default_date_range ?? 'last_30_days',
    maintenance_mode: props.settings.maintenance_mode,
    app_display_name: props.settings.app_display_name ?? props.branding.appName,
    support_email: props.settings.support_email ?? '',
    brand_primary_color: props.settings.brand_primary_color ?? props.branding.primaryColor,
    email_logo_same_as_site: props.settings.email_logo_same_as_site ?? true,
    transactional_emails_enabled: props.settings.transactional_emails_enabled ?? true,
    email_welcome_enabled: props.settings.email_welcome_enabled ?? true,
    email_password_changed_enabled: props.settings.email_password_changed_enabled ?? true,
    email_account_deactivated_enabled: props.settings.email_account_deactivated_enabled ?? true,
});

const uploading = ref(null);

const submit = () => form.put(route('admin.settings.update'));

const uploadAsset = (type, event) => {
    const file = event.target.files?.[0];
    if (!file) return;

    uploading.value = type;

    router.post(
        route('admin.settings.branding.upload', type),
        { file },
        {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => {
                uploading.value = null;
                event.target.value = '';
            },
        },
    );
};

const removeAsset = (type) => {
    if (!confirm('Remove this branding asset and revert to the default?')) {
        return;
    }

    router.delete(route('admin.settings.branding.delete', type), {
        preserveScroll: true,
    });
};

const page = usePage();

const branding = computed(() => page.props.branding ?? props.branding);

const assets = computed(() => [
    {
        type: 'site_logo',
        label: 'Site logo',
        hint: 'Used in navigation, auth pages, and marketing. PNG or SVG recommended.',
        url: branding.value.siteLogoUrl,
        hasCustom: branding.value.hasCustomSiteLogo,
    },
    {
        type: 'email_logo',
        label: 'Email logo',
        hint: 'Shown in transactional emails. Use a wide logo (~220×56px).',
        url: branding.value.emailLogoUrl,
        hasCustom: branding.value.hasCustomEmailLogo,
        hidden: form.email_logo_same_as_site,
    },
    {
        type: 'favicon',
        label: 'Favicon',
        hint: 'Browser tab icon. Square PNG or ICO works best.',
        url: branding.value.faviconUrl,
        hasCustom: branding.value.hasCustomFavicon,
    },
]);
</script>

<template>
    <Head title="Admin — Settings" />

    <AdminLayout>
        <form class="mx-auto max-w-2xl space-y-6" @submit.prevent="submit">
            <PageHeader
                title="Platform settings"
                description="Changes apply immediately across the platform."
            />
            <div class="vm-card space-y-4">
                <h3 class="vm-panel-title">Branding</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Customize how your platform appears in the app, landing page, and emails.
                </p>

                <div>
                    <InputLabel for="app_display_name" value="App display name" />
                    <input id="app_display_name" v-model="form.app_display_name" type="text" maxlength="80" class="vm-input mt-1" />
                </div>

                <div>
                    <InputLabel for="support_email" value="Support email" />
                    <input id="support_email" v-model="form.support_email" type="email" placeholder="support@example.com" class="vm-input mt-1" />
                    <p class="mt-1 text-xs text-slate-500">Shown in email footers. Leave empty to hide.</p>
                </div>

                <div>
                    <InputLabel for="brand_primary_color" value="Primary brand color" />
                    <div class="mt-1 flex items-center gap-3">
                        <input
                            id="brand_primary_color"
                            v-model="form.brand_primary_color"
                            type="color"
                            class="h-10 w-14 cursor-pointer rounded-lg border border-slate-200 bg-white p-1 dark:border-slate-600"
                        />
                        <input v-model="form.brand_primary_color" type="text" pattern="^#[0-9A-Fa-f]{6}$" class="vm-input max-w-[8rem] font-mono text-sm uppercase" />
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Used for browser theme color and accent highlights.</p>
                </div>

                <label class="flex items-center gap-3">
                    <input v-model="form.email_logo_same_as_site" type="checkbox" class="rounded border-slate-300 text-indigo-600 dark:border-slate-600 dark:bg-slate-800" />
                    <span class="text-sm text-slate-700 dark:text-slate-300">Use site logo in emails</span>
                </label>

                <div class="space-y-4 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <div
                        v-for="asset in assets"
                        :key="asset.type"
                        v-show="!asset.hidden"
                        class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 dark:border-slate-700 dark:bg-slate-800/40"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ asset.label }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ asset.hint }}</p>
                            </div>
                            <div class="flex h-14 min-w-[5rem] items-center justify-center rounded-lg border border-slate-200 bg-white px-3 dark:border-slate-600 dark:bg-slate-900">
                                <img :src="asset.url" :alt="asset.label" class="max-h-10 max-w-[8rem] object-contain" />
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <label class="vm-btn-secondary cursor-pointer text-sm">
                                {{ uploading === asset.type ? 'Uploading…' : 'Upload' }}
                                <input
                                    type="file"
                                    accept="image/png,image/jpeg,image/webp,image/svg+xml,.ico"
                                    class="hidden"
                                    :disabled="uploading === asset.type"
                                    @change="uploadAsset(asset.type, $event)"
                                />
                            </label>
                            <button
                                v-if="asset.hasCustom"
                                type="button"
                                class="text-sm font-medium text-rose-600 hover:text-rose-500 dark:text-rose-400"
                                @click="removeAsset(asset.type)"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="vm-card space-y-4">
                <h3 class="vm-panel-title">Limits</h3>
                <div>
                    <InputLabel for="max_sites_per_user" value="Max sites per user" />
                    <input id="max_sites_per_user" v-model.number="form.max_sites_per_user" type="number" min="1" max="100" class="vm-input mt-1" />
                </div>
            </div>

            <div class="vm-card space-y-4">
                <h3 class="vm-panel-title">Analytics</h3>
                <div>
                    <InputLabel for="retention_days" value="Retention days" />
                    <input id="retention_days" v-model.number="form.retention_days" type="number" min="30" max="3650" class="vm-input mt-1" />
                </div>
                <label class="flex items-center gap-3">
                    <input v-model="form.rollup_enabled" type="checkbox" class="rounded border-slate-300 text-indigo-600 dark:border-slate-600 dark:bg-slate-800" />
                    <span class="text-sm text-slate-700 dark:text-slate-300">Enable nightly rollup</span>
                </label>
                <div>
                    <InputLabel for="default_date_range" value="Default dashboard date range" />
                    <select id="default_date_range" v-model="form.default_date_range" class="vm-input mt-1">
                        <option
                            v-for="preset in DATE_RANGE_PRESETS.filter((item) => item.value !== 'custom')"
                            :key="preset.value"
                            :value="preset.value"
                        >
                            {{ preset.label }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="vm-card space-y-4">
                <h3 class="vm-panel-title">Ingestion</h3>
                <div>
                    <InputLabel for="collect_rate_limit" value="Collect rate limit (requests/min per IP)" />
                    <input id="collect_rate_limit" v-model.number="form.collect_rate_limit" type="number" min="10" max="1000" class="vm-input mt-1" />
                </div>
                <label class="flex items-center gap-3">
                    <input v-model="form.maintenance_mode" type="checkbox" class="rounded border-slate-300 text-indigo-600 dark:border-slate-600 dark:bg-slate-800" />
                    <span class="text-sm text-slate-700 dark:text-slate-300">Maintenance mode (503 on collect API)</span>
                </label>
            </div>

            <div class="vm-card space-y-4">
                <h3 class="vm-panel-title">Transactional emails</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Optional notifications sent to users. Email verification and password reset always send when triggered.
                </p>
                <label class="flex items-center gap-3">
                    <input
                        v-model="form.transactional_emails_enabled"
                        type="checkbox"
                        class="rounded border-slate-300 text-indigo-600 dark:border-slate-600 dark:bg-slate-800"
                    />
                    <span class="text-sm text-slate-700 dark:text-slate-300">Enable transactional emails</span>
                </label>
                <div class="space-y-3 border-l-2 border-slate-200 pl-4 dark:border-slate-700" :class="{ 'opacity-50': !form.transactional_emails_enabled }">
                    <label class="flex items-center gap-3">
                        <input
                            v-model="form.email_welcome_enabled"
                            type="checkbox"
                            :disabled="!form.transactional_emails_enabled"
                            class="rounded border-slate-300 text-indigo-600 disabled:cursor-not-allowed dark:border-slate-600 dark:bg-slate-800"
                        />
                        <span class="text-sm text-slate-700 dark:text-slate-300">Welcome email after email verification</span>
                    </label>
                    <label class="flex items-center gap-3">
                        <input
                            v-model="form.email_password_changed_enabled"
                            type="checkbox"
                            :disabled="!form.transactional_emails_enabled"
                            class="rounded border-slate-300 text-indigo-600 disabled:cursor-not-allowed dark:border-slate-600 dark:bg-slate-800"
                        />
                        <span class="text-sm text-slate-700 dark:text-slate-300">Password changed alert</span>
                    </label>
                    <label class="flex items-center gap-3">
                        <input
                            v-model="form.email_account_deactivated_enabled"
                            type="checkbox"
                            :disabled="!form.transactional_emails_enabled"
                            class="rounded border-slate-300 text-indigo-600 disabled:cursor-not-allowed dark:border-slate-600 dark:bg-slate-800"
                        />
                        <span class="text-sm text-slate-700 dark:text-slate-300">Account deactivated notice</span>
                    </label>
                </div>
            </div>

            <div class="vm-card space-y-4">
                <h3 class="vm-panel-title">Registration</h3>
                <label class="flex items-center gap-3">
                    <input v-model="form.registration_enabled" type="checkbox" class="rounded border-slate-300 text-indigo-600 dark:border-slate-600 dark:bg-slate-800" />
                    <span class="text-sm text-slate-700 dark:text-slate-300">Allow new user signups</span>
                </label>
            </div>

            <button type="submit" class="vm-btn-primary" :disabled="form.processing">Save settings</button>
        </form>
    </AdminLayout>
</template>
