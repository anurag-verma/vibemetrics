<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useClientFormValidation } from '@/Composables/useClientFormValidation';
import {
    useForgotPasswordFieldWatch,
    validateEmailLive,
} from '@/Composables/useLiveValidation';
import { validateEmail } from '@/utils/validation';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const { clientErrors, submitted, fieldError, liveOptions } = useClientFormValidation(form);

useForgotPasswordFieldWatch(form, clientErrors, submitted);

const submit = () => {
    submitted.value = true;

    validateEmailLive(clientErrors, form.email, { onBlur: true, submitted: true });

    const result = validateEmail(form.email);

    if (! result.valid) {
        clientErrors.value = {
            ...clientErrors.value,
            email: result.message,
        };
        return;
    }

    if (result.normalized) {
        form.email = result.normalized;
    }

    form.post(route('password.email'), {
        onError: () => {
            clientErrors.value = {};
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div class="mb-4 text-sm text-gray-600">
            Forgot your password? No problem. Just let us know your email
            address and we will email you a password reset link that will allow
            you to choose a new one.
        </div>

        <div
            v-if="status"
            class="mb-4 text-sm font-medium text-green-600"
        >
            {{ status }}
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
                    class="mt-1 block w-full"
                    v-model="form.email"
                    autofocus
                    autocomplete="username"
                    @blur="validateEmailLive(clientErrors, form.email, liveOptions({ onBlur: true }))"
                />

                <InputError class="mt-2" :message="fieldError('email')" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Email Password Reset Link
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
