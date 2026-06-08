import { ref } from 'vue';

export function useClientFormValidation(form) {
    const clientErrors = ref({});
    const submitted = ref(false);

    const fieldError = (field) => form.errors[field] || clientErrors.value[field];

    const liveOptions = (extra = {}) => ({
        submitted: submitted.value,
        ...extra,
    });

    return {
        clientErrors,
        submitted,
        fieldError,
        liveOptions,
    };
}
