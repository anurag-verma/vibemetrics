<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdatePreferencesForm from './Partials/UpdatePreferencesForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    timezones: {
        type: Array,
        default: () => [],
    },
    dateRangePresets: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Profile" />

    <AppLayout>
        <div class="mx-auto max-w-3xl space-y-6">
            <PageHeader title="Profile" description="Update your account details, preferences, and security settings." />
            <div class="vm-card">
                <UpdateProfileInformationForm
                    area="user"
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>
            <div class="vm-card">
                <UpdatePreferencesForm :timezones="timezones" :date-range-presets="dateRangePresets" class="max-w-xl" />
            </div>
            <div class="vm-card">
                <UpdatePasswordForm class="max-w-xl" />
            </div>
            <div class="vm-card">
                <DeleteUserForm area="user" class="max-w-xl" />
            </div>
        </div>
    </AppLayout>
</template>
