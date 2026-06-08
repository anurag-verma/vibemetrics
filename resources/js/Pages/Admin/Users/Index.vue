<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    users: Object,
    siteLimit: Number,
    currentUserId: Number,
});

const userToDelete = ref(null);
const deleting = ref(false);

const updateUser = (user, payload) => {
    router.patch(route('admin.users.update', user.id), payload, { preserveScroll: true });
};

const toggleAdmin = (user) => updateUser(user, { is_admin: !user.is_admin });
const toggleVerified = (user) => updateUser(user, { email_verified: !user.email_verified });
const toggleActive = (user) => updateUser(user, { is_active: !user.is_active });

const confirmDelete = (user) => {
    userToDelete.value = user;
};

const closeDeleteModal = () => {
    if (deleting.value) {
        return;
    }

    userToDelete.value = null;
};

const deleteUser = () => {
    if (!userToDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(route('admin.users.destroy', userToDelete.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            userToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="Admin — Users" />

    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Users</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Global site limit: {{ siteLimit }} per user</p>
            </div>
        </template>

        <div class="vm-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500 dark:border-slate-700 dark:text-slate-400">
                            <th class="pb-3 pr-4 font-medium">Name</th>
                            <th class="pb-3 pr-4 font-medium">Email</th>
                            <th class="pb-3 pr-4 font-medium">Sites</th>
                            <th class="pb-3 pr-4 font-medium">Registered</th>
                            <th class="pb-3 pr-4 font-medium">Verified</th>
                            <th class="pb-3 pr-4 font-medium">Active</th>
                            <th class="pb-3 pr-4 font-medium">Admin</th>
                            <th class="pb-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="user in users.data" :key="user.id" class="text-slate-700 dark:text-slate-300">
                            <td class="py-3 pr-4 font-medium">{{ user.name }}</td>
                            <td class="py-3 pr-4">{{ user.email }}</td>
                            <td class="py-3 pr-4">{{ user.sites_count }} / {{ siteLimit }}</td>
                            <td class="py-3 pr-4 text-slate-500 dark:text-slate-400">{{ user.created_at }}</td>
                            <td class="py-3 pr-4">
                                <button
                                    type="button"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition"
                                    :class="user.email_verified ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700'"
                                    :title="user.email_verified ? 'Mark as unverified' : 'Mark as verified'"
                                    @click="toggleVerified(user)"
                                >
                                    <span
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition"
                                        :class="user.email_verified ? 'translate-x-5' : 'translate-x-0'"
                                    />
                                </button>
                            </td>
                            <td class="py-3 pr-4">
                                <button
                                    type="button"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition"
                                    :class="user.is_active ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700'"
                                    :disabled="user.id === currentUserId && user.is_active"
                                    :title="user.id === currentUserId && user.is_active ? 'Cannot disable your own account' : (user.is_active ? 'Disable user' : 'Enable user')"
                                    @click="toggleActive(user)"
                                >
                                    <span
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition"
                                        :class="user.is_active ? 'translate-x-5' : 'translate-x-0'"
                                    />
                                </button>
                            </td>
                            <td class="py-3 pr-4">
                                <button
                                    type="button"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition"
                                    :class="user.is_admin ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-700'"
                                    :disabled="user.id === currentUserId && user.is_admin"
                                    :title="user.id === currentUserId && user.is_admin ? 'Cannot remove your own admin access' : ''"
                                    @click="toggleAdmin(user)"
                                >
                                    <span
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition"
                                        :class="user.is_admin ? 'translate-x-5' : 'translate-x-0'"
                                    />
                                </button>
                            </td>
                            <td class="py-3">
                                <button
                                    type="button"
                                    class="text-sm font-medium transition"
                                    :class="user.id === currentUserId
                                        ? 'cursor-not-allowed text-slate-300 dark:text-slate-600'
                                        : 'text-rose-600 hover:text-rose-500 dark:text-rose-400 dark:hover:text-rose-300'"
                                    :disabled="user.id === currentUserId"
                                    :title="user.id === currentUserId ? 'Cannot delete your own account' : 'Delete user'"
                                    @click="confirmDelete(user)"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="users.links?.length > 3" class="mt-4 flex flex-wrap gap-2 border-t border-slate-200 pt-4 dark:border-slate-700">
                <a
                    v-for="link in users.links"
                    :key="link.label"
                    :href="link.url"
                    class="rounded-lg px-3 py-1 text-sm"
                    :class="link.active ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800'"
                    v-html="link.label"
                />
            </div>
        </div>

        <div
            v-if="userToDelete"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeDeleteModal"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Delete user?</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    This will permanently delete
                    <span class="font-medium text-slate-900 dark:text-white">{{ userToDelete.name }}</span>
                    ({{ userToDelete.email }}) and all of their sites, page views, and statistics.
                    This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="vm-btn-secondary" :disabled="deleting" @click="closeDeleteModal">
                        Cancel
                    </button>
                    <button type="button" class="vm-btn-danger" :disabled="deleting" @click="deleteUser">
                        {{ deleting ? 'Deleting…' : 'Delete user' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
