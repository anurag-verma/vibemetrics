import chromeIcon from '@browser-logos/chrome/chrome.svg?url';
import edgeIcon from '@browser-logos/edge/edge.svg?url';
import firefoxIcon from '@browser-logos/firefox/firefox.svg?url';
import operaIcon from '@browser-logos/opera/opera.svg?url';
import safariIcon from '@browser-logos/safari/safari.svg?url';
import { siAndroid } from 'simple-icons';

/** @type {Record<string, string>} */
const browserIcons = {
    chrome: chromeIcon,
    firefox: firefoxIcon,
    safari: safariIcon,
    edge: edgeIcon,
    opera: operaIcon,
};

/** @type {Record<string, { path: string, hex: string }>} */
const osBrands = {
    windows: {
        path: 'M3 4.5 10.5 3.4V11H3V4.5Zm0 7.5h7.5v7.6L3 18.5V12ZM11.25 3.2 21 1.5v9.75H11.25V3.2ZM11.25 12.75H21V22l-9.75-1.65V12.75Z',
        hex: '0078D4',
    },
    android: { path: siAndroid.path, hex: siAndroid.hex },
};

/** @type {Set<string>} */
const osAppIcons = new Set(['ios', 'macos', 'linux']);

/**
 * @param {string} label
 * @returns {string}
 */
function normalizeKey(label) {
    return (label || '').trim().toLowerCase();
}

/**
 * @param {string} label
 * @returns {string|null}
 */
export function countryFlagClass(label) {
    const code = normalizeKey(label);

    if (code.length !== 2 || code === 'xx') {
        return null;
    }

    return `fi fi-${code}`;
}

/**
 * @param {'browser'|'os'|'device'|'country'} type
 * @param {string} label
 * @returns {{ kind: 'flag'|'image'|'brand'|'os-app'|'glyph', value?: string, path?: string, hex?: string }|null}
 */
export function resolveAnalyticsIcon(type, label) {
    const key = normalizeKey(label);

    if (!key) {
        return { kind: 'glyph', value: type === 'country' ? 'unknown-location' : 'unknown' };
    }

    if (type === 'country') {
        if (key === 'xx') {
            return { kind: 'glyph', value: 'unknown-location' };
        }

        const flagClass = countryFlagClass(label);

        return flagClass ? { kind: 'flag', value: flagClass } : { kind: 'glyph', value: 'unknown-location' };
    }

    if (type === 'browser') {
        if (key === 'unknown') {
            return { kind: 'glyph', value: 'unknown' };
        }

        const src = browserIcons[key];

        return src ? { kind: 'image', value: src } : { kind: 'glyph', value: 'unknown' };
    }

    if (type === 'os') {
        if (key === 'unknown') {
            return { kind: 'glyph', value: 'unknown-os' };
        }

        if (osAppIcons.has(key)) {
            return { kind: 'os-app', value: key };
        }

        const brand = osBrands[key];

        return brand
            ? { kind: 'brand', path: brand.path, hex: brand.hex }
            : { kind: 'glyph', value: 'unknown-os' };
    }

    if (type === 'device') {
        if (['desktop', 'mobile', 'tablet'].includes(key)) {
            return { kind: 'glyph', value: key };
        }

        if (key === 'unknown') {
            return { kind: 'glyph', value: 'unknown' };
        }
    }

    return { kind: 'glyph', value: 'unknown' };
}

/** @type {Record<string, string>} */
export const osDisplayNames = {
    windows: 'Windows',
    ios: 'iOS',
    android: 'Android',
    macos: 'macOS',
    linux: 'Linux',
    unknown: 'Unknown',
};
