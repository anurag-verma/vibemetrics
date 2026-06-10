import androidIcon from '@browser-logos/android/android_32x32.png?url';
import chromeIcon from '@browser-logos/chrome/chrome.svg?url';
import edgeIcon from '@browser-logos/edge/edge.svg?url';
import firefoxIcon from '@browser-logos/firefox/firefox.svg?url';
import operaIcon from '@browser-logos/opera/opera.svg?url';
import safariIcon from '@browser-logos/safari/safari.svg?url';

/** @type {Record<string, string>} */
const browserIcons = {
    Chrome: chromeIcon,
    Firefox: firefoxIcon,
    Safari: safariIcon,
    Edge: edgeIcon,
    Opera: operaIcon,
};

/** @type {Record<string, string>} */
const osIcons = {
    Android: androidIcon,
    iOS: 'apple',
    macOS: 'apple',
    Windows: 'windows',
    Linux: 'linux',
};

/**
 * @param {string} label
 * @returns {string|null}
 */
export function countryFlagClass(label) {
    const code = (label || '').toLowerCase();

    if (code.length !== 2 || code === 'xx') {
        return null;
    }

    return `fi fi-${code}`;
}

/**
 * @param {'browser'|'os'|'device'} type
 * @param {string} label
 * @returns {{ kind: 'flag'|'image'|'glyph', value: string }|null}
 */
export function resolveAnalyticsIcon(type, label) {
    if (!label) {
        return null;
    }

    if (type === 'country') {
        const flagClass = countryFlagClass(label);

        return flagClass ? { kind: 'flag', value: flagClass } : null;
    }

    if (type === 'browser') {
        const src = browserIcons[label];

        return src ? { kind: 'image', value: src } : null;
    }

    if (type === 'os') {
        const icon = osIcons[label];

        if (!icon) {
            return null;
        }

        if (icon === 'apple' || icon === 'windows' || icon === 'linux') {
            return { kind: 'glyph', value: icon };
        }

        return { kind: 'image', value: icon };
    }

    if (type === 'device') {
        const normalized = label.toLowerCase();

        if (['desktop', 'mobile', 'tablet'].includes(normalized)) {
            return { kind: 'glyph', value: normalized };
        }
    }

    return null;
}
