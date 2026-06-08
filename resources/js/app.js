import '../css/app.css';
import './bootstrap';

import { initTheme } from '@/Composables/useTheme';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

initTheme();

const appName = import.meta.env.VITE_APP_NAME || 'VibeMetrics';
let displayAppName = appName;

const syncDisplayName = (page) => {
    const name = page?.props?.branding?.appName;
    if (name) {
        displayAppName = name;
    }
};

createInertiaApp({
    title: (title) => (title ? `${title} - ${displayAppName}` : displayAppName),
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#6366f1',
    },
});

router.on('navigate', (event) => syncDisplayName(event.detail.page));
router.on('success', (event) => syncDisplayName(event.detail.page));

router.on('invalid', (event) => {
    if (event.detail.response?.status === 419) {
        event.preventDefault();
        window.location.assign('/login');
    }
});

const initialPage = document.getElementById('app')?.dataset?.page;
if (initialPage) {
    try {
        syncDisplayName(JSON.parse(initialPage));
    } catch {
        // ignore malformed bootstrap payload
    }
}
