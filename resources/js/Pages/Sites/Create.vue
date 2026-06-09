<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    siteLimit: { type: Number, default: null },
    isUnlimitedSites: { type: Boolean, default: false },
    sitesUsed: Number,
});

const form = useForm({
    name: '',
    domain: '',
});

const submit = () => form.post(route('sites.store'));
</script>

<template>
    <Head title="Add Website" />

    <AppLayout>
        <div class="mx-auto max-w-xl">
            <PageHeader
                title="Add website"
                :description="isUnlimitedSites
                    ? `${sitesUsed} ${sitesUsed === 1 ? 'site' : 'sites'} on your account`
                    : `${sitesUsed} of ${siteLimit} sites used`"
            />
            <form class="vm-card space-y-6" @submit.prevent="submit">
                <div>
                    <InputLabel for="name" value="Site name" />
                    <TextInput id="name" v-model="form.name" class="vm-input mt-1" required placeholder="My Website" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="domain" value="Domain" />
                    <TextInput id="domain" v-model="form.domain" class="vm-input mt-1" required placeholder="example.com" />
                    <p class="mt-1 text-xs text-slate-400">Without https:// — e.g. example.com</p>
                    <InputError class="mt-2" :message="form.errors.domain" />
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="vm-btn-primary" :disabled="form.processing">Create website</button>
                    <Link :href="route('sites.index')" class="vm-btn-secondary">Cancel</Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
