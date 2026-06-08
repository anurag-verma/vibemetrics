<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import PasswordMatchFeedback from '@/Components/PasswordMatchFeedback.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useClientFormValidation } from '@/Composables/useClientFormValidation';
import {
    validateEmailLive,
    validateNameLive,
    validatePasswordConfirmationLive,
    validatePasswordLive,
    validateRegisterFieldsLive,
    useRegisterFieldWatches,
} from '@/Composables/useLiveValidation';
import { validateRegister } from '@/utils/validation';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const { clientErrors, submitted, fieldError, liveOptions } = useClientFormValidation(form);

useRegisterFieldWatches(form, clientErrors, submitted);

const submit = () => {
    submitted.value = true;

    validateRegisterFieldsLive(clientErrors, form.data(), { onBlur: true, submitted: true });

    const result = validateRegister(form.data());

    if (! result.valid) {
        clientErrors.value = {
            ...clientErrors.value,
            ...result.errors,
        };
        return;
    }

    if (result.normalized?.name) {
        form.name = result.normalized.name;
    }

    if (result.normalized?.email) {
        form.email = result.normalized.email;
    }

    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
        onError: () => {
            clientErrors.value = {};
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Create account" />

        <h1 class="mb-1 text-center text-xl font-bold text-slate-900">Get started free</h1>
        <p class="mb-6 text-center text-sm text-slate-500">Create your VibeMetrics account</p>

        <form class="space-y-4" novalidate @submit.prevent="submit">
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
                    type="text"
                    inputmode="email"
                    autocapitalize="off"
                    autocorrect="off"
                    spellcheck="false"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    autocomplete="username"
                    @blur="validateEmailLive(clientErrors, form.email, liveOptions({ onBlur: true }))"
                />

                <InputError class="mt-2" :message="fieldError('email')" />
            </div>

            <div>
                <InputLabel for="password" value="Password" />

                <div class="mt-1">
                    <PasswordInput
                        id="password"
                        v-model="form.password"
                        input-class="mt-0"
                        autocomplete="new-password"
                        show-requirements
                        @blur="validatePasswordLive(clientErrors, form.password, liveOptions({ onBlur: true }))"
                    />
                </div>

                <InputError class="mt-2" :message="fieldError('password')" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />

                <div class="mt-1">
                    <PasswordInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        input-class="mt-0"
                        autocomplete="new-password"
                        @blur="validatePasswordConfirmationLive(
                            clientErrors,
                            form.password,
                            form.password_confirmation,
                            liveOptions({ onBlur: true }),
                        )"
                    />

                    <PasswordMatchFeedback
                        :password="form.password"
                        :confirmation="form.password_confirmation"
                    />
                </div>

                <InputError class="mt-2" :message="fieldError('password_confirmation')" />
            </div>

            <div class="flex items-center justify-end pt-1">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
