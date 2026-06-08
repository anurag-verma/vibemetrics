import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const fallback = {
    appName: 'VibeMetrics',
    supportEmail: null,
    primaryColor: '#4f46e5',
    siteLogoUrl: '/images/vibemetrics.png',
    emailLogoUrl: '/images/vibemetrics.png',
    faviconUrl: '/images/vibemetrics.png',
    emailLogoSameAsSite: true,
    hasCustomSiteLogo: false,
    hasCustomEmailLogo: false,
    hasCustomFavicon: false,
};

export function useBranding() {
    const page = usePage();

    const branding = computed(() => ({
        ...fallback,
        ...(page.props.branding ?? {}),
    }));

    return { branding };
}
