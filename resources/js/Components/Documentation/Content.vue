<script setup>
import DocsSectionNav from '@/Components/DocsSectionNav.vue';
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
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
    { id: 'custom-events', label: 'Custom events' },
    { id: 'goals', label: 'Goal tracking' },
    { id: 'dashboard', label: 'Dashboard' },
    { id: 'privacy', label: 'Privacy' },
];

const customEventExamples = [
    { name: 'Button click', code: "Vibemetrics.track('button_clicked', { label: 'Get started' })" },
    { name: 'Form submit', code: "Vibemetrics.track('form_submitted', { form: 'contact' })" },
    { name: 'Purchase', code: "Vibemetrics.track('purchase_completed', { plan: 'pro' })" },
    { name: 'Video play', code: "Vibemetrics.track('video_played')" },
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
    <div class="min-w-0">
        <div class="grid gap-6 lg:grid-cols-[200px_minmax(0,1fr)] lg:gap-12">
            <DocsSectionNav :sections="sections" variant="app" />

            <div class="min-w-0 overflow-x-hidden space-y-8 sm:space-y-10">
            <section id="getting-started" class="scroll-mt-36 lg:scroll-mt-24">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Getting started</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    Three steps from zero to live analytics on your site.
                </p>

                <ol class="mt-6 space-y-4">
                    <li class="vm-card flex gap-4 p-4 sm:p-5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">1</span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-900 dark:text-white">Add your website</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                Open <Link :href="route('sites.index')" class="text-indigo-600 hover:underline dark:text-indigo-400">Websites</Link>
                                and add your domain. VibeMetrics generates a unique tracking ID for each property.
                            </p>
                        </div>
                    </li>
                    <li class="vm-card flex gap-4 p-4 sm:p-5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">2</span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-900 dark:text-white">Install the tracker</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                Copy the embed snippet from your site settings and paste it before the closing
                                <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800">&lt;/body&gt;</code> tag.
                            </p>
                        </div>
                    </li>
                    <li class="vm-card flex gap-4 p-4 sm:p-5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">3</span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-900 dark:text-white">Verify in the dashboard</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                Visit your site, then check the overview for pageviews, referrers, and live visitors.
                                Data usually appears within seconds.
                            </p>
                        </div>
                    </li>
                </ol>
            </section>

            <section id="install" class="scroll-mt-36 lg:scroll-mt-24">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Install script</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    Replace <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800">YOUR_TRACKING_ID</code>
                    with the ID from your site settings.
                </p>

                <div class="vm-code-block mt-4">
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

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="vm-card p-4">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">WordPress / Webflow / Ghost</p>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Paste the snippet in your theme footer or custom code injection area.</p>
                    </div>
                    <div class="vm-card p-4">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Shopify</p>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Add the script via <strong>Online Store → Themes → Edit code → theme.liquid</strong> before <code class="text-xs">&lt;/body&gt;</code>.</p>
                    </div>
                </div>
            </section>

            <section id="spa" class="scroll-mt-36 lg:scroll-mt-24">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">SPA support</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    VibeMetrics automatically tracks route changes in single-page applications.
                    No extra setup is required for Vue, React, Next.js, or other frameworks using the History API.
                </p>
                <ul class="mt-4 space-y-3">
                    <li class="vm-card flex items-start gap-3 px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span><strong class="text-slate-900 dark:text-white">pushState / replaceState</strong> — client-side navigations are captured as page views.</span>
                    </li>
                    <li class="vm-card flex items-start gap-3 px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span><strong class="text-slate-900 dark:text-white">Under 2KB script</strong> — async and deferred so it never blocks rendering.</span>
                    </li>
                    <li class="vm-card flex items-start gap-3 px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span><strong class="text-slate-900 dark:text-white">Bot filtering</strong> — automated traffic is excluded so your numbers stay honest.</span>
                    </li>
                </ul>
            </section>

            <section id="utm" class="scroll-mt-36 lg:scroll-mt-24">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">UTM tracking</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    Standard UTM query parameters are captured automatically and appear in your campaign reports.
                </p>

                <div class="vm-card mt-4 overflow-x-auto">
                    <table class="min-w-[32rem] divide-y divide-slate-200 text-sm sm:min-w-full dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-white">Parameter</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-white">Example</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-white">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <tr v-for="row in utmParams" :key="row.param">
                                <td class="px-4 py-3 font-mono text-xs text-indigo-600 dark:text-indigo-400">{{ row.param }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-500 dark:text-slate-400">{{ row.example }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ row.description }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
                    Example link:
                    <code class="mt-2 block overflow-x-auto rounded-lg bg-slate-100 px-3 py-2 text-xs text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                        https://yoursite.com/pricing?utm_source=twitter&amp;utm_medium=social&amp;utm_campaign=launch
                    </code>
                </p>
            </section>

            <section id="custom-events" class="scroll-mt-36 lg:scroll-mt-24">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Custom events</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    Track any action beyond page views — button clicks, form submissions, purchases, video plays, and more.
                    <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800">Vibemetrics.track()</code> is available on every page that already has the tracker snippet. No extra setup needed.
                </p>

                <div class="vm-card mt-4 p-4 sm:p-5">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">API</p>
                    <pre class="mt-3 overflow-x-auto rounded-lg bg-slate-100 px-4 py-3 font-mono text-xs text-slate-800 dark:bg-slate-800 dark:text-slate-300">Vibemetrics.track(eventName, props?)</pre>
                    <div class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                        <div class="flex gap-3">
                            <code class="shrink-0 rounded bg-slate-100 px-1.5 py-0.5 text-xs text-indigo-600 dark:bg-slate-800 dark:text-indigo-400">eventName</code>
                            <span>Required. A short string identifying the action, e.g. <code class="text-xs">"signup_completed"</code>.</span>
                        </div>
                        <div class="flex gap-3">
                            <code class="shrink-0 rounded bg-slate-100 px-1.5 py-0.5 text-xs text-indigo-600 dark:bg-slate-800 dark:text-indigo-400">props</code>
                            <span>Optional. A plain object with up to 20 key-value pairs for extra context, e.g. <code class="text-xs">{ plan: 'pro' }</code>.</span>
                        </div>
                    </div>
                </div>

                <p class="mt-6 text-sm font-semibold text-slate-900 dark:text-white">Examples</p>
                <div class="mt-3 space-y-3">
                    <div v-for="ex in customEventExamples" :key="ex.name" class="vm-card p-4">
                        <p class="mb-2 text-xs font-medium text-slate-500 dark:text-slate-400">{{ ex.name }}</p>
                        <pre class="overflow-x-auto font-mono text-xs text-slate-800 dark:text-slate-300">{{ ex.code }}</pre>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="vm-card border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                        <p class="font-semibold">Important</p>
                        <p class="mt-1">The tracker loads with <code class="text-xs">defer</code>. Call <code class="text-xs">Vibemetrics.track()</code> inside event listeners or after <code class="text-xs">DOMContentLoaded</code> — not at the top of the page.</p>
                    </div>
                    <div class="vm-card p-4 text-sm text-slate-600 dark:text-slate-400">
                        <p class="font-semibold text-slate-900 dark:text-white">Where to see results</p>
                        <p class="mt-1">Events appear in the <strong>Custom Events</strong> table at the bottom of your dashboard, grouped by name and filtered by date range.</p>
                    </div>
                </div>
            </section>

            <section id="goals" class="scroll-mt-36 lg:scroll-mt-24">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Goal tracking</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    Goals measure how many visitors reach a key page — a thank-you page, signup confirmation, or pricing page — and calculate your conversion rate automatically.
                    No code changes needed: goals work against your existing page view history.
                </p>

                <ol class="mt-4 space-y-4">
                    <li class="vm-card flex gap-4 p-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">1</span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-900 dark:text-white">Open Site Settings</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                Go to <strong>Sites → Edit</strong> for the site you want to track. Scroll to the <strong>Goals</strong> section and click <strong>Add goal</strong>.
                            </p>
                        </div>
                    </li>
                    <li class="vm-card flex gap-4 p-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">2</span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-900 dark:text-white">Define the URL</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                Give the goal a name and enter a URL pattern.
                                Use <strong>Exact</strong> to match a specific URL, or <strong>Contains</strong> to match any URL with a string in it — useful for dynamic paths like <code class="text-xs">/order/</code>.
                            </p>
                        </div>
                    </li>
                    <li class="vm-card flex gap-4 p-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">3</span>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-900 dark:text-white">See conversions instantly</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                The <strong>Goals</strong> panel on your dashboard shows completions, unique completions, and conversion rate for the selected date range — calculated against your existing data.
                            </p>
                        </div>
                    </li>
                </ol>

                <div class="vm-card mt-4 overflow-x-auto">
                    <table class="min-w-[28rem] divide-y divide-slate-200 text-sm sm:min-w-full dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-white">Match type</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-white">Pattern</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-900 dark:text-white">Matches</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs text-indigo-600 dark:text-indigo-400">exact</td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-500 dark:text-slate-400">/thank-you</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">Only <code class="text-xs">yoursite.com/thank-you</code></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs text-indigo-600 dark:text-indigo-400">contains</td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-500 dark:text-slate-400">/order/</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">Any URL with <code class="text-xs">/order/</code> in the path, e.g. <code class="text-xs">/order/123/confirm</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="dashboard" class="scroll-mt-36 lg:scroll-mt-24">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Dashboard</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    Once tracking is live, your dashboard shows everything you need at a glance.
                </p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="vm-card p-5">
                        <h3 class="font-semibold text-slate-900 dark:text-white">Overview metrics</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Page views, unique visitors, bounce rate, and session duration over your selected date range.</p>
                    </div>
                    <div class="vm-card p-5">
                        <h3 class="font-semibold text-slate-900 dark:text-white">Top pages & referrers</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">See which content performs and where your audience comes from.</p>
                    </div>
                    <div class="vm-card p-5">
                        <h3 class="font-semibold text-slate-900 dark:text-white">Live visitors</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Real-time count of active visitors on your site right now.</p>
                    </div>
                    <div class="vm-card p-5">
                        <h3 class="font-semibold text-slate-900 dark:text-white">CSV export</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Download aggregated stats and raw page view rows for any date range directly from the dashboard.</p>
                    </div>
                    <div class="vm-card p-5">
                        <h3 class="font-semibold text-slate-900 dark:text-white">Custom events</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">See a breakdown of every custom event fired via <code class="text-xs">Vibemetrics.track()</code>, grouped by name and filtered by date range.</p>
                    </div>
                    <div class="vm-card p-5">
                        <h3 class="font-semibold text-slate-900 dark:text-white">Goal conversions</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Track how many visitors reach key pages and see your conversion rate across any date range.</p>
                    </div>
                </div>
            </section>

            <section id="privacy" class="vm-card scroll-mt-36 p-4 sm:p-6 lg:scroll-mt-24">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Privacy</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    VibeMetrics is built privacy-first. No cookies, no IP address storage, and no cross-site tracking.
                    Bot traffic is filtered automatically.
                </p>
                <a
                    :href="route('privacy')"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                >
                    Read the privacy policy
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </section>
            </div>
        </div>
    </div>
</template>
