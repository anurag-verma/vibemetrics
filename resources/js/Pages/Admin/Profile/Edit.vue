<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});
</script>

<template>
    <Head title="Admin — Profile" />

    <AdminLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Profile</h1>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">
            <div class="vm-card">
                <UpdateProfileInformationForm
                    area="admin"
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
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
