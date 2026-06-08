<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useClientFormValidation } from '@/Composables/useClientFormValidation';
import {
    validateEmailLive,
    validatePasswordLive,
    useLoginFieldWatches,
} from '@/Composables/useLiveValidation';
import { validateLogin } from '@/utils/validation';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    supportEmail: {
        type: String,
        default: '',
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const { clientErrors, submitted, fieldError, liveOptions } = useClientFormValidation(form);

useLoginFieldWatches(form, clientErrors, submitted);

const submit = () => {
    submitted.value = true;

    validateEmailLive(clientErrors, form.email, { onBlur: true, submitted: true });
    validatePasswordLive(clientErrors, form.password, { onBlur: true, submitted: true });

    const result = validateLogin(form.data());

    if (! result.valid) {
        clientErrors.value = {
            ...clientErrors.value,
            ...result.errors,
        };
        return;
    }

    if (result.normalized?.email) {
        form.email = result.normalized.email;
    }

    form.post(route('login'), {
        onFinish: () => form.reset('password'),
        onError: () => {
            clientErrors.value = {};
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <h1 class="mb-1 text-center text-xl font-bold text-slate-900">Welcome back</h1>
        <p class="mb-6 text-center text-sm text-slate-500">Sign in to your VibeMetrics account</p>

        <div v-if="status" class="mb-4 rounded-lg bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700">
            {{ status }}
        </div>

        <div
            v-if="form.errors.login_failed"
            class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3"
            role="alert"
        >
            <p class="text-sm font-semibold text-rose-900">Sign in failed</p>
            <p class="mt-1 text-sm leading-relaxed text-rose-800">
                {{ form.errors.login_failed }}
            </p>
        </div>

        <div
            v-if="form.errors.account_disabled"
            class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3"
            role="alert"
        >
            <p class="text-sm font-semibold text-amber-900">Account unavailable</p>
            <p class="mt-1 text-sm leading-relaxed text-amber-800">
                Your VibeMetrics account has been deactivated and you can't sign in right now.
                If you believe this is a mistake or need your account restored, please
                <a
                    v-if="supportEmail"
                    :href="`mailto:${supportEmail}`"
                    class="font-medium text-amber-900 underline decoration-amber-400 underline-offset-2 hover:text-amber-950"
                >
                    contact our support team
                </a>
                <span v-else>contact our support team</span>.
            </p>
        </div>

        <form novalidate @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="text"
                    inputmode="email"
                    autocapitalize="off"
                    autocorrect="off"
                    spellcheck="false"
                    class="mt-1 block w-full rounded-xl"
                    v-model="form.email"
                    autofocus
                    autocomplete="username"
                    @blur="validateEmailLive(clientErrors, form.email, liveOptions({ onBlur: true }))"
                />

                <InputError class="mt-2" :message="fieldError('email')" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <div class="mt-1">
                    <PasswordInput
                        id="password"
                        v-model="form.password"
                        input-class="rounded-xl mt-0"
                        autocomplete="current-password"
                        @blur="validatePasswordLive(clientErrors, form.password, liveOptions({ onBlur: true }))"
                    />

                    <InputError class="mt-2" :message="fieldError('password')" />
                </div>
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600"
                        >Remember me</span
                    >
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-indigo-600 hover:text-indigo-500"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
