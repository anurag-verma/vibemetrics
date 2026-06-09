<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import PageHeader from '@/Components/PageHeader.vue';
import ToggleSwitch from '@/Components/ToggleSwitch.vue';
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

const setVerified = (user, value) => updateUser(user, { email_verified: value });
const setActive = (user, value) => updateUser(user, { is_active: value });
const setAdmin = (user, value) => updateUser(user, { is_admin: value });

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
        <PageHeader
            title="Users"
            :description="`Global site limit: ${siteLimit} per user`"
        />

        <div class="vm-card overflow-hidden">
            <div class="space-y-4 p-4 md:hidden">
                <div
                    v-for="user in users.data"
                    :key="`card-${user.id}`"
                    class="rounded-xl border border-slate-200 p-4 dark:border-slate-700"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 dark:text-white">{{ user.name }}</p>
                            <p class="mt-0.5 truncate text-sm text-slate-500 dark:text-slate-400">{{ user.email }}</p>
                        </div>
                        <button
                            type="button"
                            class="shrink-0 text-sm font-medium transition"
                            :class="user.id === currentUserId
                                ? 'cursor-not-allowed text-slate-300 dark:text-slate-600'
                                : 'text-rose-600 hover:text-rose-500 dark:text-rose-400'"
                            :disabled="user.id === currentUserId"
                            @click="confirmDelete(user)"
                        >
                            Delete
                        </button>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Sites</dt>
                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-300">{{ user.sites_count }} / {{ siteLimit }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Registered</dt>
                            <dd class="mt-0.5 text-slate-600 dark:text-slate-400">{{ user.created_at }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex flex-wrap gap-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                            <ToggleSwitch
                                :model-value="user.email_verified"
                                label="Email verified"
                                @update:model-value="setVerified(user, $event)"
                            />
                            Verified
                        </label>
                        <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                            <ToggleSwitch
                                :model-value="user.is_active"
                                label="Account active"
                                :disabled="user.id === currentUserId && user.is_active"
                                @update:model-value="setActive(user, $event)"
                            />
                            Active
                        </label>
                        <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                            <ToggleSwitch
                                :model-value="user.is_admin"
                                label="Admin access"
                                :disabled="user.id === currentUserId && user.is_admin"
                                @update:model-value="setAdmin(user, $event)"
                            />
                            Admin
                        </label>
                    </div>
                </div>
            </div>

            <div class="hidden overflow-x-auto md:block">
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
                                <ToggleSwitch
                                    :model-value="user.email_verified"
                                    :label="user.email_verified ? 'Mark as unverified' : 'Mark as verified'"
                                    @update:model-value="setVerified(user, $event)"
                                />
                            </td>
                            <td class="py-3 pr-4">
                                <ToggleSwitch
                                    :model-value="user.is_active"
                                    :label="user.id === currentUserId && user.is_active ? 'Cannot disable your own account' : (user.is_active ? 'Disable user' : 'Enable user')"
                                    :disabled="user.id === currentUserId && user.is_active"
                                    @update:model-value="setActive(user, $event)"
                                />
                            </td>
                            <td class="py-3 pr-4">
                                <ToggleSwitch
                                    :model-value="user.is_admin"
                                    :label="user.id === currentUserId && user.is_admin ? 'Cannot remove your own admin access' : 'Toggle admin access'"
                                    :disabled="user.id === currentUserId && user.is_admin"
                                    @update:model-value="setAdmin(user, $event)"
                                />
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

        <Modal :show="Boolean(userToDelete)" max-width="md" @close="closeDeleteModal">
            <div v-if="userToDelete" class="p-6">
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
        </Modal>
    </AdminLayout>
</template>
