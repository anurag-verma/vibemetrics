<script setup>
import CloudDecoration from '@/Components/Marketing/CloudDecoration.vue';
import GradientMesh from '@/Components/Marketing/GradientMesh.vue';
import PaperBackground from '@/Components/Marketing/PaperBackground.vue';
import ScrollReveal from '@/Components/Marketing/ScrollReveal.vue';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const features = [
    'Multiple sites per account',
    'Page views, visitors & live count',
    'Top pages, referrers & UTM campaigns',
    'SPA route tracking',
    'CSV export',
    'Bot filtering & privacy-first tracking',
    'No cookies · No credit card required',
];

const faqs = [
    {
        q: 'Is VibeMetrics really free?',
        a: 'Yes. The full product is free right now — create an account, add your sites, and start tracking immediately.',
    },
    {
        q: 'Will paid plans be added later?',
        a: 'We may introduce paid tiers in the future for advanced needs, but the core experience stays accessible. Early users keep their free access.',
    },
    {
        q: 'Are there usage limits?',
        a: 'Free accounts include generous limits for personal sites, blogs, and small teams. Check your dashboard for current site limits.',
    },
];
</script>

<template>
    <Head title="Pricing" />

    <MarketingLayout :can-login="canLogin" :can-register="canRegister">
        <section class="vm-section-mesh relative overflow-hidden">
            <PaperBackground />
            <GradientMesh variant="hero" />
            <CloudDecoration position="hero" />

            <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-24 lg:px-8">
                <ScrollReveal direction="up">
                    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Pricing</p>
                    <h1 class="mt-4 font-serif text-4xl font-bold tracking-tight text-warm-800 sm:text-5xl">
                        Free
                        <span class="vm-gradient-text">forever</span>
                    </h1>
                    <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-slate-600">
                        Honest analytics without a price tag. Everything you need to understand your traffic —
                        no trials, no credit card, no surprise upgrades.
                    </p>
                </ScrollReveal>
            </div>
        </section>

        <section class="vm-section-mesh-alt relative py-16 sm:py-20">
            <PaperBackground variant="alt" />

            <div class="mx-auto max-w-lg px-4 sm:px-6 lg:px-8">
                <ScrollReveal direction="up">
                    <div class="vm-craft-card relative overflow-hidden p-8 sm:p-10">
                        <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200/60 bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-indigo-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                            Current plan
                        </span>

                        <h2 class="mt-6 font-serif text-2xl font-bold text-warm-800">Free</h2>
                        <div class="mt-3 flex items-baseline gap-2">
                            <span class="font-serif text-5xl font-bold text-warm-800">$0</span>
                            <span class="text-slate-500">forever</span>
                        </div>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            For personal sites, blogs, side projects, and small teams getting started with privacy-first analytics.
                        </p>

                        <ul class="mt-8 space-y-3">
                            <li v-for="feature in features" :key="feature" class="flex items-start gap-3 text-sm text-slate-600">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ feature }}
                            </li>
                        </ul>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="vm-btn-primary mt-10 block w-full py-3.5 text-center text-base"
                        >
                            Get started free
                        </Link>
                        <Link
                            v-else
                            :href="route('login')"
                            class="vm-btn-primary mt-10 block w-full py-3.5 text-center text-base"
                        >
                            Log in
                        </Link>

                        <p class="mt-4 text-center text-xs text-slate-500">Setup in under 2 minutes</p>
                    </div>
                </ScrollReveal>
            </div>
        </section>

        <section class="vm-section-mesh relative py-16 sm:py-24">
            <PaperBackground />

            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <ScrollReveal direction="up">
                    <h2 class="text-center font-serif text-2xl font-bold text-warm-800 sm:text-3xl">Common questions</h2>
                </ScrollReveal>

                <div class="mt-10 space-y-4">
                    <ScrollReveal
                        v-for="(item, index) in faqs"
                        :key="item.q"
                        direction="up"
                        :delay="index * 60"
                    >
                        <div class="vm-craft-card p-6">
                            <h3 class="font-semibold text-warm-800">{{ item.q }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ item.a }}</p>
                        </div>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <section class="vm-section-mesh-alt relative py-16 sm:py-20">
            <PaperBackground variant="alt" />
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
                <ScrollReveal direction="up">
                    <h2 class="vm-section-heading">Start tracking today</h2>
                    <p class="mt-4 text-lg text-slate-600">Add one script and see your first pageviews in minutes.</p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                        <Link v-if="canRegister" :href="route('register')" class="vm-btn-primary px-8 py-3 text-base">
                            Try for free
                        </Link>
                        <Link :href="route('docs')" class="vm-btn-secondary px-8 py-3 text-base">
                            View documentation
                        </Link>
                    </div>
                </ScrollReveal>
            </div>
        </section>
    </MarketingLayout>
</template>
