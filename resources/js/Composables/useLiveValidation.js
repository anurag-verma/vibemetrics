import { ref, watch } from 'vue';
import {
    currentPasswordErrorMessage,
    emailErrorMessage,
    passwordConfirmationErrorMessage,
    passwordErrorMessage,
    personNameErrorMessage,
    validatePersonName,
} from '@/utils/validation';

export function setClientFieldError(clientErrors, field, message) {
    const next = { ...clientErrors.value };

    if (message) {
        next[field] = message;
    } else {
        delete next[field];
    }

    clientErrors.value = next;
}

export function validateNameLive(clientErrors, value, options = {}) {
    const message = personNameErrorMessage(value, options);
    setClientFieldError(clientErrors, 'name', message);
}

export function validateEmailLive(clientErrors, value, options = {}) {
    const message = emailErrorMessage(value, options);
    setClientFieldError(clientErrors, 'email', message);
}

export function validatePasswordLive(clientErrors, value, options = {}) {
    const message = passwordErrorMessage(value, options);
    setClientFieldError(clientErrors, 'password', message);
}

export function validatePasswordConfirmationLive(
    clientErrors,
    password,
    confirmation,
    options = {},
) {
    const message = passwordConfirmationErrorMessage(password, confirmation, options);
    setClientFieldError(clientErrors, 'password_confirmation', message);
}

export function validateCurrentPasswordLive(clientErrors, value, options = {}) {
    const message = currentPasswordErrorMessage(value, options);
    setClientFieldError(clientErrors, 'current_password', message);
}

export function validateRegisterFieldsLive(clientErrors, fields, options = {}) {
    validateNameLive(clientErrors, fields.name, options);
    validateEmailLive(clientErrors, fields.email, options);
    validatePasswordLive(clientErrors, fields.password, options);
    validatePasswordConfirmationLive(
        clientErrors,
        fields.password,
        fields.password_confirmation,
        options,
    );
}

export function validateLoginFieldsLive(clientErrors, fields, options = {}) {
    validateEmailLive(clientErrors, fields.email, options);
    validatePasswordLive(clientErrors, fields.password, options);
}

export function validatePersonNameOnSubmit(name) {
    return validatePersonName(name);
}

export function useRegisterFieldWatches(form, clientErrors, submitted) {
    watch(
        () => form.name,
        (value) => validateNameLive(clientErrors, value, { submitted: submitted.value }),
    );

    watch(
        () => form.email,
        (value) => validateEmailLive(clientErrors, value, { submitted: submitted.value }),
    );

    watch(
        () => form.password,
        (value) => {
            validatePasswordLive(clientErrors, value, { submitted: submitted.value });
            validatePasswordConfirmationLive(
                clientErrors,
                value,
                form.password_confirmation,
                { submitted: submitted.value },
            );
        },
    );

    watch(
        () => form.password_confirmation,
        (value) => validatePasswordConfirmationLive(
            clientErrors,
            form.password,
            value,
            { submitted: submitted.value },
        ),
    );
}

export function useLoginFieldWatches(form, clientErrors, submitted) {
    watch(
        () => form.email,
        (value) => validateEmailLive(clientErrors, value, { submitted: submitted.value }),
    );

    watch(
        () => form.password,
        (value) => validatePasswordLive(clientErrors, value, { submitted: submitted.value }),
    );
}

export function useProfileNameFieldWatch(form, clientErrors, submitted) {
    watch(
        () => form.name,
        (value) => validateNameLive(clientErrors, value, { submitted: submitted.value }),
    );
}

export function usePasswordResetFieldWatches(form, clientErrors, submitted) {
    watch(
        () => form.password,
        (value) => {
            validatePasswordLive(clientErrors, value, { submitted: submitted.value });
            validatePasswordConfirmationLive(
                clientErrors,
                value,
                form.password_confirmation,
                { submitted: submitted.value },
            );
        },
    );

    watch(
        () => form.password_confirmation,
        (value) => validatePasswordConfirmationLive(
            clientErrors,
            form.password,
            value,
            { submitted: submitted.value },
        ),
    );
}

export function useUpdatePasswordFieldWatches(form, clientErrors, submitted) {
    watch(
        () => form.current_password,
        (value) => validateCurrentPasswordLive(clientErrors, value, { submitted: submitted.value }),
    );

    watch(
        () => form.password,
        (value) => {
            validatePasswordLive(clientErrors, value, { submitted: submitted.value });
            validatePasswordConfirmationLive(
                clientErrors,
                value,
                form.password_confirmation,
                { submitted: submitted.value },
            );
        },
    );

    watch(
        () => form.password_confirmation,
        (value) => validatePasswordConfirmationLive(
            clientErrors,
            form.password,
            value,
            { submitted: submitted.value },
        ),
    );
}

export function useForgotPasswordFieldWatch(form, clientErrors, submitted) {
    watch(
        () => form.email,
        (value) => validateEmailLive(clientErrors, value, { submitted: submitted.value }),
    );
}
