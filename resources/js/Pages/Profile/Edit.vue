<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});
</script>

<template>
    <Head title="Profile" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Profile</h1>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">
            <div class="vm-card">
                <UpdateProfileInformationForm
                    area="user"
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
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
