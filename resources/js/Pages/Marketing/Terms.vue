<script setup>
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Terms of Service" />

    <MarketingLayout :can-login="canLogin" :can-register="canRegister">
        <section class="py-16 sm:py-24">
            <div class="mx-auto max-w-3xl px-4 sm:px-6">
                <h1 class="text-4xl font-bold tracking-tight text-slate-900">Terms of Service</h1>
                <p class="mt-2 text-sm text-slate-500">Last updated: June 6, 2026</p>

                <div class="prose prose-slate mt-10 max-w-none text-slate-600">
                    <h2 class="text-lg font-semibold text-slate-900">1. Acceptance</h2>
                    <p>By using VibeMetrics, you agree to these terms. If you do not agree, do not use the service.</p>

                    <h2 class="mt-8 text-lg font-semibold text-slate-900">2. Service description</h2>
                    <p>VibeMetrics provides privacy-focused web analytics. You may only track sites you own or have permission to monitor.</p>

                    <h2 class="mt-8 text-lg font-semibold text-slate-900">3. Acceptable use</h2>
                    <p>You agree not to use VibeMetrics to track sites you do not own, collect sensitive personal data, or violate applicable privacy laws.</p>

                    <h2 class="mt-8 text-lg font-semibold text-slate-900">4. Account responsibility</h2>
                    <p>You are responsible for maintaining the security of your account credentials and for all activity under your account.</p>

                    <h2 class="mt-8 text-lg font-semibold text-slate-900">5. Availability</h2>
                    <p>We strive for high availability but do not guarantee uninterrupted service. Scheduled maintenance may occur.</p>

                    <h2 class="mt-8 text-lg font-semibold text-slate-900">6. Limitation of liability</h2>
                    <p>VibeMetrics is provided "as is" without warranties. We are not liable for indirect or consequential damages arising from use of the service.</p>

                    <h2 class="mt-8 text-lg font-semibold text-slate-900">7. Contact</h2>
                    <p>Legal inquiries: <a href="mailto:legal@vibemetrics.com" class="text-indigo-600 hover:underline">legal@vibemetrics.com</a></p>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>
