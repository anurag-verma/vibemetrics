<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdatePreferencesForm from '@/Pages/Profile/Partials/UpdatePreferencesForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
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
    <Head title="Admin — Profile" />

    <AdminLayout>
        <div class="mx-auto max-w-3xl space-y-6">
            <PageHeader title="Profile" description="Update your admin account details and security settings." />
            <div class="vm-card">
                <UpdateProfileInformationForm
                    area="admin"
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>
            <div class="vm-card">
                <UpdatePreferencesForm area="admin" :timezones="timezones" :date-range-presets="dateRangePresets" class="max-w-xl" />
            </div>
            <div class="vm-card">
                <UpdatePasswordForm class="max-w-xl" />
            </div>
            <div class="vm-card">
                <DeleteUserForm area="admin" class="max-w-xl" />
            </div>
        </div>
    </AdminLayout>
</template>
