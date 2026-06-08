<script setup>
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Privacy Policy" />

    <MarketingLayout :can-login="canLogin" :can-register="canRegister">
        <section class="py-16 sm:py-24">
            <div class="mx-auto max-w-3xl px-4 sm:px-6">
                <h1 class="text-4xl font-bold tracking-tight text-slate-900">Privacy Policy</h1>
                <p class="mt-2 text-sm text-slate-500">Last updated: June 6, 2026</p>

                <div class="prose prose-slate mt-10 max-w-none text-slate-600">
                    <h2 class="text-lg font-semibold text-slate-900">Our commitment</h2>
                    <p>VibeMetrics is built privacy-first. We believe analytics should not require tracking individuals across the web.</p>

                    <h2 class="mt-8 text-lg font-semibold text-slate-900">What we collect</h2>
                    <p>When a visitor loads a page with the VibeMetrics script, we collect:</p>
                    <ul class="list-disc pl-6">
                        <li>Page URL and referrer</li>
                        <li>Browser, operating system, and device type (derived from User-Agent or screen width)</li>
                        <li>Country (from CDN/proxy headers when available — we do not store IP addresses)</li>
                        <li>UTM campaign parameters when present in the URL</li>
                    </ul>

                    <h2 class="mt-8 text-lg font-semibold text-slate-900">What we do NOT collect</h2>
                    <ul class="list-disc pl-6">
                        <li>IP addresses</li>
                        <li>Cookies or local storage identifiers</li>
                        <li>Personally identifiable information</li>
                        <li>Cross-site browsing history</li>
                    </ul>

                    <h2 class="mt-8 text-lg font-semibold text-slate-900">Data retention</h2>
                    <p>Raw page view events are retained according to your plan settings (default 365 days), then automatically purged. Aggregated daily statistics may be kept longer for historical reporting.</p>

                    <h2 class="mt-8 text-lg font-semibold text-slate-900">Your rights</h2>
                    <p>Account holders may export their data via CSV and delete their account at any time. Contact us for data-related requests.</p>

                    <h2 class="mt-8 text-lg font-semibold text-slate-900">Contact</h2>
                    <p>Questions? Email <a href="mailto:privacy@vibemetrics.com" class="text-indigo-600 hover:underline">privacy@vibemetrics.com</a></p>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>
