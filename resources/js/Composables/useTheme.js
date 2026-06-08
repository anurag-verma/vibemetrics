const STORAGE_KEY = 'vibemetrics-theme';

/** @typedef {'system' | 'light' | 'dark'} ThemeMode */

/** @returns {ThemeMode} */
export function getStoredTheme() {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored === 'light' || stored === 'dark' || stored === 'system') {
        return stored;
    }
    return 'system';
}

/** @param {ThemeMode} mode */
export function setStoredTheme(mode) {
    localStorage.setItem(STORAGE_KEY, mode);
    applyTheme(mode);
}

/** @param {ThemeMode} mode */
export function applyTheme(mode) {
    const root = document.documentElement;
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = mode === 'dark' || (mode === 'system' && prefersDark);
    root.classList.toggle('dark', isDark);
}

export function initTheme() {
    applyTheme(getStoredTheme());
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (getStoredTheme() === 'system') {
            applyTheme('system');
        }
    });
}
