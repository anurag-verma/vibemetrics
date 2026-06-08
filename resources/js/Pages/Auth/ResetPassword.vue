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
    usePasswordResetFieldWatches,
    validatePasswordConfirmationLive,
    validatePasswordLive,
} from '@/Composables/useLiveValidation';
import { isPasswordValid, validatePasswordConfirmation } from '@/utils/validation';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const { clientErrors, submitted, fieldError, liveOptions } = useClientFormValidation(form);

usePasswordResetFieldWatches(form, clientErrors, submitted);

const submit = () => {
    submitted.value = true;

    validatePasswordLive(clientErrors, form.password, { onBlur: true, submitted: true });
    validatePasswordConfirmationLive(
        clientErrors,
        form.password,
        form.password_confirmation,
        { onBlur: true, submitted: true },
    );

    if (! isPasswordValid(form.password)) {
        return;
    }

    const confirmationResult = validatePasswordConfirmation(
        form.password,
        form.password_confirmation,
    );

    if (! confirmationResult.valid) {
        clientErrors.value = {
            ...clientErrors.value,
            password_confirmation: confirmationResult.message,
        };
        return;
    }

    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
        onError: () => {
            clientErrors.value = {};
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <form novalidate @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="text"
                    inputmode="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    autofocus
                    autocomplete="username"
                    disabled
                />

                <InputError class="mt-2" :message="fieldError('email')" />
            </div>

            <div class="mt-4">
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

            <div class="mt-4">
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

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Reset Password
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
