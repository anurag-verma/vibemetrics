<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { validatePersonName } from '@/utils/validation';
import { useClientFormValidation } from '@/Composables/useClientFormValidation';
import {
    useProfileNameFieldWatch,
    validateNameLive,
} from '@/Composables/useLiveValidation';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    area: {
        type: String,
        default: 'user',
        validator: (value) => ['user', 'admin'].includes(value),
    },
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const updateRoute = props.area === 'admin' ? 'admin.profile.update' : 'profile.update';

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
});

const { clientErrors, submitted, fieldError, liveOptions } = useClientFormValidation(form);

useProfileNameFieldWatch(form, clientErrors, submitted);

const submit = () => {
    submitted.value = true;

    validateNameLive(clientErrors, form.name, { onBlur: true, submitted: true });

    const result = validatePersonName(form.name);

    if (! result.valid) {
        clientErrors.value = { name: result.message };
        return;
    }

    if (result.normalized) {
        form.name = result.normalized;
    }

    form.patch(route(updateRoute), {
        onError: () => {
            clientErrors.value = {};
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-slate-400">
                Update your display name. Your email address is fixed for account security.
            </p>
        </header>

        <form
            novalidate
            @submit.prevent="submit"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    autofocus
                    autocomplete="name"
                    @blur="validateNameLive(clientErrors, form.name, liveOptions({ onBlur: true }))"
                />

                <InputError class="mt-2" :message="fieldError('name')" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full opacity-70"
                    :model-value="user.email"
                    disabled
                    autocomplete="username"
                />

                <p class="mt-2 text-sm text-gray-500 dark:text-slate-400">
                    Contact support if you need to change your email address.
                </p>

                <div v-if="mustVerifyEmail && user.email_verified_at === null">
                    <p class="mt-2 text-sm text-gray-800 dark:text-slate-300">
                        Your email address is unverified.
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-slate-400 dark:hover:text-white dark:focus:ring-offset-slate-900"
                        >
                            Click here to re-send the verification email.
                        </Link>
                    </p>

                    <div
                        v-show="status === 'verification-link-sent'"
                        class="mt-2 text-sm font-medium text-green-600"
                    >
                        A new verification link has been sent to your email address.
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
            </div>
        </form>
    </section>
</template>
