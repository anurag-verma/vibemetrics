export const NAME_INVALID_CHARS_MESSAGE = 'Please remove any numbers or special symbols from your name.';

export const NAME_REQUIRED_MESSAGE = 'Please enter your name.';

export const EMAIL_REQUIRED_MESSAGE = 'Please enter your email address.';

export const EMAIL_INVALID_MESSAGE = 'Please enter a valid email address.';

export const PASSWORD_REQUIRED_MESSAGE = 'Please enter your password.';

export const PASSWORD_CONFIRMATION_REQUIRED_MESSAGE = 'Please confirm your password.';

export const PASSWORD_MISMATCH_MESSAGE = 'Passwords do not match.';

export const CURRENT_PASSWORD_REQUIRED_MESSAGE = 'Please enter your current password.';

const PERSON_NAME_PATTERN = /^[\p{L}'-]+(?: [\p{L}'-]+)*$/u;

const DISALLOWED_NAME_CHARS = /[\d]|[^\p{L}\s'\-]/u;

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export function normalizePersonName(name) {
    if (name == null) {
        return '';
    }

    return name.trim().replace(/\s+/g, ' ');
}

export function hasInvalidNameCharacters(value) {
    return DISALLOWED_NAME_CHARS.test(String(value ?? ''));
}

export function shouldShowRequiredError({ onBlur = false, submitted = false } = {}) {
    return onBlur || submitted;
}

export function personNameErrorMessage(raw, { onBlur = false, submitted = false } = {}) {
    const text = String(raw ?? '');

    if (! text.trim()) {
        return shouldShowRequiredError({ onBlur, submitted }) ? NAME_REQUIRED_MESSAGE : null;
    }

    if (hasInvalidNameCharacters(text)) {
        return NAME_INVALID_CHARS_MESSAGE;
    }

    const normalized = normalizePersonName(text);

    if (normalized && PERSON_NAME_PATTERN.test(normalized)) {
        return null;
    }

    return shouldShowRequiredError({ onBlur, submitted }) ? NAME_REQUIRED_MESSAGE : null;
}

export function emailErrorMessage(raw, { onBlur = false, submitted = false } = {}) {
    const trimmed = String(raw ?? '').trim();

    if (! trimmed) {
        return shouldShowRequiredError({ onBlur, submitted }) ? EMAIL_REQUIRED_MESSAGE : null;
    }

    if (EMAIL_PATTERN.test(trimmed)) {
        return null;
    }

    return EMAIL_INVALID_MESSAGE;
}

export function passwordErrorMessage(raw, { onBlur = false, submitted = false } = {}) {
    if (! String(raw ?? '').trim()) {
        return shouldShowRequiredError({ onBlur, submitted }) ? PASSWORD_REQUIRED_MESSAGE : null;
    }

    return null;
}

export function passwordConfirmationErrorMessage(
    password,
    confirmation,
    { onBlur = false, submitted = false } = {},
) {
    if (! String(confirmation ?? '').trim()) {
        return shouldShowRequiredError({ onBlur, submitted })
            ? PASSWORD_CONFIRMATION_REQUIRED_MESSAGE
            : null;
    }

    if (password !== confirmation) {
        return PASSWORD_MISMATCH_MESSAGE;
    }

    return null;
}

export function currentPasswordErrorMessage(raw, { onBlur = false, submitted = false } = {}) {
    if (! String(raw ?? '').trim()) {
        return shouldShowRequiredError({ onBlur, submitted })
            ? CURRENT_PASSWORD_REQUIRED_MESSAGE
            : null;
    }

    return null;
}

export function validatePersonName(name) {
    const message = personNameErrorMessage(name, { onBlur: true, submitted: true });
    const normalized = normalizePersonName(name);

    if (message) {
        return { valid: false, message, normalized };
    }

    return { valid: true, message: null, normalized };
}

export function validateEmail(email) {
    const message = emailErrorMessage(email, { onBlur: true, submitted: true });

    if (message) {
        return { valid: false, message, normalized: String(email ?? '').trim().toLowerCase() };
    }

    return { valid: true, message: null, normalized: String(email ?? '').trim().toLowerCase() };
}

export function getPasswordRequirements(password) {
    const value = password ?? '';

    return {
        minLength: value.length >= 8,
        letters: /\p{L}/u.test(value),
        mixedCase: /[a-z]/.test(value) && /[A-Z]/.test(value),
        numbers: /\d/.test(value),
    };
}

export function isPasswordValid(password) {
    const requirements = getPasswordRequirements(password);

    return Object.values(requirements).every(Boolean);
}

export function validatePassword(password) {
    return {
        valid: isPasswordValid(password),
        message: null,
    };
}

export function validatePasswordConfirmation(password, confirmation) {
    const message = passwordConfirmationErrorMessage(password, confirmation, {
        onBlur: true,
        submitted: true,
    });

    if (message) {
        return { valid: false, message };
    }

    return { valid: true, message: null };
}

export function validateLogin({ email, password }) {
    const errors = {};
    const emailMessage = emailErrorMessage(email, { onBlur: true, submitted: true });

    if (emailMessage) {
        errors.email = emailMessage;
    }

    const passwordMessage = passwordErrorMessage(password, { onBlur: true, submitted: true });

    if (passwordMessage) {
        errors.password = passwordMessage;
    }

    return {
        valid: Object.keys(errors).length === 0,
        errors,
        normalized: { email: String(email ?? '').trim().toLowerCase() },
    };
}

export function validateRegister({ name, email, password, password_confirmation }) {
    const errors = {};
    const nameMessage = personNameErrorMessage(name, { onBlur: true, submitted: true });

    if (nameMessage) {
        errors.name = nameMessage;
    }

    const emailMessage = emailErrorMessage(email, { onBlur: true, submitted: true });

    if (emailMessage) {
        errors.email = emailMessage;
    }

    const passwordMessage = passwordErrorMessage(password, { onBlur: true, submitted: true });

    if (passwordMessage) {
        errors.password = passwordMessage;
    }

    const confirmationMessage = passwordConfirmationErrorMessage(password, password_confirmation, {
        onBlur: true,
        submitted: true,
    });

    if (confirmationMessage) {
        errors.password_confirmation = confirmationMessage;
    }

    const valid = ! nameMessage
        && ! emailMessage
        && ! passwordMessage
        && ! confirmationMessage
        && isPasswordValid(password);

    return {
        valid,
        errors,
        normalized: {
            name: normalizePersonName(name),
            email: String(email ?? '').trim().toLowerCase(),
        },
    };
}
