<script setup>
import CloudDecoration from '@/Components/Marketing/CloudDecoration.vue';
import GradientMesh from '@/Components/Marketing/GradientMesh.vue';
import PaperBackground from '@/Components/Marketing/PaperBackground.vue';
import ScrollReveal from '@/Components/Marketing/ScrollReveal.vue';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    appUrl: { type: String, required: true },
});

const snippet = computed(() => {
    const host = props.appUrl;
    return `<scr${''}ipt defer data-website-id="YOUR_TRACKING_ID" data-api-host="${host}" src="${host}/js/tracker.js"></scr${''}ipt>`;
});

const utmParams = [
    { param: 'utm_source', example: 'twitter', description: 'Where traffic came from (e.g. newsletter, google).' },
    { param: 'utm_medium', example: 'social', description: 'Marketing medium (e.g. email, cpc, social).' },
    { param: 'utm_campaign', example: 'spring_launch', description: 'Campaign name for grouping performance.' },
    { param: 'utm_content', example: 'hero_cta', description: 'Optional — differentiate ads or links.' },
    { param: 'utm_term', example: 'analytics', description: 'Optional — paid search keywords.' },
];

const sections = [
    { id: 'getting-started', label: 'Getting started' },
    { id: 'install', label: 'Install script' },
    { id: 'spa', label: 'SPA support' },
    { id: 'utm', label: 'UTM tracking' },
    { id: 'dashboard', label: 'Dashboard' },
    { id: 'privacy', label: 'Privacy' },
];

const snippetRef = ref(null);
const copiedSnippet = ref(false);
let copiedSnippetTimeout = null;

const fitSnippetHeight = () => {
    const el = snippetRef.value;
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = `${el.scrollHeight}px`;
};

const copySnippet = async () => {
    await navigator.clipboard.writeText(snippet.value);
    copiedSnippet.value = true;
    if (copiedSnippetTimeout) clearTimeout(copiedSnippetTimeout);
    copiedSnippetTimeout = setTimeout(() => {
        copiedSnippet.value = false;
        copiedSnippetTimeout = null;
    }, 2000);
};

onMounted(fitSnippetHeight);
watch(snippet, fitSnippetHeight);

onUnmounted(() => {
    if (copiedSnippetTimeout) clearTimeout(copiedSnippetTimeout);
});
</script>

<template>
    <Head title="Documentation" />

    <MarketingLayout :can-login="canLogin" :can-register="canRegister">
        <section class="vm-section-mesh relative overflow-hidden">
            <PaperBackground />
            <GradientMesh variant="hero" />
            <CloudDecoration position="hero" />

            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
                <ScrollReveal direction="up">
                    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Documentation</p>
                    <h1 class="mt-4 max-w-3xl font-serif text-4xl font-bold tracking-tight text-warm-800 sm:text-5xl">
                        Set up honest analytics in
                        <span class="vm-gradient-text">minutes</span>
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-relaxed text-slate-600">
                        Add one lightweight script, verify data in your dashboard, and start measuring traffic
                        without cookies or complex configuration.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <Link v-if="canRegister" :href="route('register')" class="vm-btn-primary px-7 py-3 text-base">
                            Create free account
                        </Link>
                        <a href="#install" class="vm-btn-secondary px-7 py-3 text-base">
                            Jump to install
                        </a>
                    </div>
                </ScrollReveal>
            </div>
        </section>

        <section class="vm-section-mesh-alt relative py-16 sm:py-20">
            <PaperBackground variant="alt" />

            <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[220px_1fr] lg:gap-16 lg:px-8">
                <aside class="hidden lg:block">
                    <nav class="sticky top-24 space-y-1">
                        <a
                            v-for="item in sections"
                            :key="item.id"
                            :href="`#${item.id}`"
                            class="block rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-paper hover:text-warm-800"
                        >
                            {{ item.label }}
                        </a>
                    </nav>
                </aside>

                <div class="min-w-0 space-y-16">
                    <ScrollReveal direction="up">
                        <div id="getting-started" class="scroll-mt-24">
                            <h2 class="vm-section-heading">Getting started</h2>
                            <p class="mt-4 text-lg text-slate-600">
                                Three steps from zero to live analytics on your site.
                            </p>

                            <ol class="mt-8 space-y-4">
                                <li class="vm-craft-card flex gap-4 p-5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">1</span>
                                    <div>
                                        <h3 class="font-semibold text-warm-800">Create your account</h3>
                                        <p class="mt-1 text-sm leading-relaxed text-slate-600">
                                            Register for free, then open <strong>Sites</strong> and add your domain.
                                            VibeMetrics generates a unique tracking ID for each property.
                                        </p>
                                    </div>
                                </li>
                                <li class="vm-craft-card flex gap-4 p-5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">2</span>
                                    <div>
                                        <h3 class="font-semibold text-warm-800">Install the tracker</h3>
                                        <p class="mt-1 text-sm leading-relaxed text-slate-600">
                                            Copy the embed snippet from your site settings and paste it before the closing
                                            <code class="rounded bg-warm-100 px-1.5 py-0.5 text-xs">&lt;/body&gt;</code> tag.
                                        </p>
                                    </div>
                                </li>
                                <li class="vm-craft-card flex gap-4 p-5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">3</span>
                                    <div>
                                        <h3 class="font-semibold text-warm-800">Verify in the dashboard</h3>
                                        <p class="mt-1 text-sm leading-relaxed text-slate-600">
                                            Visit your site, then check the dashboard for pageviews, referrers, and live visitors.
                                            Data usually appears within seconds.
                                        </p>
                                    </div>
                                </li>
                            </ol>
                        </div>
                    </ScrollReveal>

                    <ScrollReveal direction="up">
                        <div id="install" class="scroll-mt-24">
                            <h2 class="vm-section-heading">Install script</h2>
                            <p class="mt-4 text-slate-600">
                                Replace <code class="rounded bg-warm-100 px-1.5 py-0.5 text-xs">YOUR_TRACKING_ID</code>
                                with the ID from your site settings.
                            </p>

                            <div class="vm-code-block mt-6">
                                <button
                                    type="button"
                                    class="vm-code-copy"
                                    :title="copiedSnippet ? 'Copied!' : 'Copy'"
                                    @click="copySnippet"
                                >
                                    <svg v-if="copiedSnippet" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                                <textarea
                                    ref="snippetRef"
                                    readonly
                                    :value="snippet"
                                    rows="3"
                                    class="vm-code-textarea"
                                    @focus="$event.target.select()"
                                />
                            </div>

                            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                <div class="rounded-xl border border-warm-200 bg-paper p-4">
                                    <p class="text-sm font-semibold text-warm-800">WordPress / Webflow / Ghost</p>
                                    <p class="mt-1 text-sm text-slate-600">Paste the snippet in your theme footer or custom code injection area.</p>
                                </div>
                                <div class="rounded-xl border border-warm-200 bg-paper p-4">
                                    <p class="text-sm font-semibold text-warm-800">Shopify</p>
                                    <p class="mt-1 text-sm text-slate-600">Add the script via <strong>Online Store → Themes → Edit code → theme.liquid</strong> before <code class="text-xs">&lt;/body&gt;</code>.</p>
                                </div>
                            </div>
                        </div>
                    </ScrollReveal>

                    <ScrollReveal direction="up">
                        <div id="spa" class="scroll-mt-24">
                            <h2 class="vm-section-heading">SPA support</h2>
                            <p class="mt-4 text-slate-600">
                                VibeMetrics automatically tracks route changes in single-page applications.
                                No extra setup is required for Vue, React, Next.js, or other frameworks using the History API.
                            </p>
                            <ul class="mt-6 space-y-3">
                                <li class="flex items-start gap-3 rounded-xl border border-warm-200 bg-paper px-4 py-3 text-sm text-slate-600">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span><strong class="text-warm-800">pushState / replaceState</strong> — client-side navigations are captured as page views.</span>
                                </li>
                                <li class="flex items-start gap-3 rounded-xl border border-warm-200 bg-paper px-4 py-3 text-sm text-slate-600">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span><strong class="text-warm-800">Under 2KB script</strong> — async and deferred so it never blocks rendering.</span>
                                </li>
                                <li class="flex items-start gap-3 rounded-xl border border-warm-200 bg-paper px-4 py-3 text-sm text-slate-600">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span><strong class="text-warm-800">Bot filtering</strong> — automated traffic is excluded so your numbers stay honest.</span>
                                </li>
                            </ul>
                        </div>
                    </ScrollReveal>

                    <ScrollReveal direction="up">
                        <div id="utm" class="scroll-mt-24">
                            <h2 class="vm-section-heading">UTM tracking</h2>
                            <p class="mt-4 text-slate-600">
                                Standard UTM query parameters are captured automatically and appear in your campaign reports.
                            </p>

                            <div class="mt-6 overflow-hidden rounded-2xl border border-warm-200">
                                <table class="min-w-full divide-y divide-warm-200 text-sm">
                                    <thead class="bg-paper">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-semibold text-warm-800">Parameter</th>
                                            <th class="px-4 py-3 text-left font-semibold text-warm-800">Example</th>
                                            <th class="px-4 py-3 text-left font-semibold text-warm-800">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-warm-200 bg-white">
                                        <tr v-for="row in utmParams" :key="row.param">
                                            <td class="px-4 py-3 font-mono text-xs text-indigo-700">{{ row.param }}</td>
                                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ row.example }}</td>
                                            <td class="px-4 py-3 text-slate-600">{{ row.description }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <p class="mt-4 text-sm text-slate-500">
                                Example link:
                                <code class="mt-2 block overflow-x-auto rounded-lg bg-warm-100 px-3 py-2 text-xs text-warm-800">
                                    https://yoursite.com/pricing?utm_source=twitter&amp;utm_medium=social&amp;utm_campaign=launch
                                </code>
                            </p>
                        </div>
                    </ScrollReveal>

                    <ScrollReveal direction="up">
                        <div id="dashboard" class="scroll-mt-24">
                            <h2 class="vm-section-heading">Dashboard</h2>
                            <p class="mt-4 text-slate-600">
                                Once tracking is live, your dashboard shows everything you need at a glance.
                            </p>
                            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                <div class="vm-craft-card p-5">
                                    <h3 class="font-semibold text-warm-800">Overview metrics</h3>
                                    <p class="mt-2 text-sm text-slate-600">Page views, unique visitors, bounce rate, and session duration over your selected date range.</p>
                                </div>
                                <div class="vm-craft-card p-5">
                                    <h3 class="font-semibold text-warm-800">Top pages & referrers</h3>
                                    <p class="mt-2 text-sm text-slate-600">See which content performs and where your audience comes from.</p>
                                </div>
                                <div class="vm-craft-card p-5">
                                    <h3 class="font-semibold text-warm-800">Live visitors</h3>
                                    <p class="mt-2 text-sm text-slate-600">Real-time count of active visitors on your site right now.</p>
                                </div>
                                <div class="vm-craft-card p-5">
                                    <h3 class="font-semibold text-warm-800">CSV export</h3>
                                    <p class="mt-2 text-sm text-slate-600">Download pageview and referrer data for reporting or client deliverables.</p>
                                </div>
                            </div>
                        </div>
                    </ScrollReveal>

                    <ScrollReveal direction="up">
                        <div id="privacy" class="vm-craft-card scroll-mt-24 p-8">
                            <h2 class="vm-section-heading">Privacy</h2>
                            <p class="mt-4 text-slate-600">
                                VibeMetrics is built privacy-first. No cookies, no IP address storage, and no cross-site tracking.
                                Bot traffic is filtered automatically.
                            </p>
                            <Link :href="route('privacy')" class="vm-link-arrow mt-6">
                                Read the privacy policy
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <section class="vm-section-mesh relative py-16 sm:py-20">
            <PaperBackground />
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
                <ScrollReveal direction="up">
                    <h2 class="vm-section-heading">Need help getting set up?</h2>
                    <p class="mt-4 text-lg text-slate-600">Create your account and add your first site in under two minutes.</p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                        <Link v-if="canRegister" :href="route('register')" class="vm-btn-primary px-8 py-3 text-base">
                            Try for free
                        </Link>
                        <Link :href="route('use-cases')" class="vm-btn-secondary px-8 py-3 text-base">
                            Browse use cases
                        </Link>
                    </div>
                </ScrollReveal>
            </div>
        </section>
    </MarketingLayout>
</template>
