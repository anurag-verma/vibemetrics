<script setup>
import CloudDecoration from '@/Components/Marketing/CloudDecoration.vue';
import DashboardHeroMock from '@/Components/Marketing/DashboardHeroMock.vue';
import DataControlDemo from '@/Components/Marketing/DataControlDemo.vue';
import EventTrackingDemo from '@/Components/Marketing/EventTrackingDemo.vue';
import InsightsDashboardDemo from '@/Components/Marketing/InsightsDashboardDemo.vue';
import PrivacyComplianceDemo from '@/Components/Marketing/PrivacyComplianceDemo.vue';
import GradientMesh from '@/Components/Marketing/GradientMesh.vue';
import PaperBackground from '@/Components/Marketing/PaperBackground.vue';
import FeatureGrid from '@/Components/Marketing/FeatureGrid.vue';
import ScrollReveal from '@/Components/Marketing/ScrollReveal.vue';
import UseCaseTabs from '@/Components/Marketing/UseCaseTabs.vue';
import { gridFeatures } from '@/data/features';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const zigzagFeatures = [
    {
        id: 'track',
        eyebrow: 'Event tracking',
        title: 'Track every visit',
        description: 'See page views, SPA route changes, referrers, and UTM campaigns in one place. Bot traffic is filtered automatically so your numbers stay honest.',
        link: { label: 'See how tracking works', href: 'docs' },
        imageFirst: false,
        mesh: 'indigo',
    },
    {
        id: 'insights',
        eyebrow: 'Dashboard',
        title: 'Deliver clear insights',
        description: 'A beautiful dashboard with live visitor counts, daily trends, top pages, and referrer breakdowns — everything you need to understand your traffic at a glance.',
        link: { label: 'Explore the dashboard', href: 'docs' },
        imageFirst: true,
        mesh: 'violet',
    },
    {
        id: 'privacy',
        eyebrow: 'Compliance',
        title: 'Privacy by design',
        description: 'No cookies, no IP storage, no cross-site tracking. VibeMetrics is built for GDPR-friendly analytics so you can measure what matters without surveillance.',
        link: { label: 'Read our privacy approach', href: 'privacy' },
        imageFirst: false,
        mesh: 'indigo',
    },
    {
        id: 'export',
        eyebrow: 'Data control',
        title: 'Export and stay compliant',
        description: 'Download CSV reports, configure data retention, and rely on nightly rollups for fast queries. Your data, your rules.',
        link: { label: 'View data features', href: 'docs' },
        imageFirst: true,
        mesh: 'violet',
    },
];

const finalChecks = [
    'No credit card required',
    'Free forever plan',
    'Setup in 2 minutes',
];

</script>

<template>
    <Head title="Privacy-First Web Analytics" />

    <MarketingLayout :can-login="canLogin" :can-register="canRegister">
        <!-- Hero -->
        <section class="vm-section-mesh relative overflow-hidden">
            <PaperBackground />
            <GradientMesh variant="hero" />
            <CloudDecoration position="hero" />

            <div class="mx-auto grid max-w-7xl items-center gap-8 px-4 py-12 sm:px-6 sm:py-14 lg:grid-cols-2 lg:gap-10 lg:py-20 lg:px-8">
                <div>
                    <ScrollReveal direction="up" :delay="100">
                        <h1 class="font-serif text-4xl font-bold tracking-tight text-warm-800 sm:text-5xl lg:text-[3rem] lg:leading-tight">
                            Your space for
                            <span class="vm-gradient-text">honest analytics</span>
                        </h1>
                    </ScrollReveal>

                    <ScrollReveal direction="up" :delay="200">
                        <p class="mt-4 text-lg leading-relaxed text-slate-600">
                            A privacy-first, lightweight alternative to Google Analytics.
                            One script, a beautiful dashboard, and zero compromise on your visitors' privacy.
                        </p>
                    </ScrollReveal>

                    <ScrollReveal direction="up" :delay="300">
                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <Link v-if="canRegister" :href="route('register')" class="vm-btn-primary px-7 py-3 text-base">
                                Try for free
                            </Link>
                            <Link :href="route('docs')" class="vm-btn-secondary px-7 py-3 text-base">
                                View documentation
                            </Link>
                        </div>
                    </ScrollReveal>
                </div>

                <ScrollReveal direction="right" :delay="200">
                    <DashboardHeroMock />
                </ScrollReveal>
            </div>
        </section>

        <!-- Use-case tabs -->
        <section class="vm-section-mesh-alt relative py-16 sm:py-20">
            <PaperBackground variant="alt" />
            <CloudDecoration position="left" />

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <ScrollReveal direction="up">
                    <div class="text-center">
                        <h2 class="vm-section-heading">VibeMetrics isn't just for one thing</h2>
                        <p class="mx-auto mt-3 max-w-xl text-lg text-slate-600">It's for your traffic, your campaigns, and your growth.</p>
                    </div>
                </ScrollReveal>
                <ScrollReveal direction="up" :delay="100">
                    <div class="mt-10">
                        <UseCaseTabs />
                    </div>
                </ScrollReveal>
            </div>
        </section>

        <!-- Zigzag features -->
        <section
            v-for="(feature, index) in zigzagFeatures"
            :key="feature.id"
            :class="[
                index % 2 === 1 ? 'vm-section-mesh-alt' : 'vm-section-mesh',
                'relative py-20 sm:py-28',
            ]"
        >
            <PaperBackground :variant="index % 2 === 1 ? 'alt' : 'default'" />
            <GradientMesh :variant="feature.mesh" />
            <CloudDecoration :position="feature.imageFirst ? 'right' : 'left'" />

            <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:gap-20 lg:px-8">
                <ScrollReveal
                    :direction="feature.imageFirst ? 'left' : 'right'"
                    :delay="100"
                    :class="feature.imageFirst ? 'lg:order-1' : 'lg:order-2'"
                >
                    <EventTrackingDemo v-if="feature.id === 'track'" />
                    <InsightsDashboardDemo v-else-if="feature.id === 'insights'" />
                    <PrivacyComplianceDemo v-else-if="feature.id === 'privacy'" />
                    <DataControlDemo v-else-if="feature.id === 'export'" />
                </ScrollReveal>

                <ScrollReveal
                    :direction="feature.imageFirst ? 'right' : 'left'"
                    :delay="200"
                    :class="feature.imageFirst ? 'lg:order-2' : 'lg:order-1'"
                >
                    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">{{ feature.eyebrow }}</p>
                    <h2 class="mt-3 vm-section-heading">{{ feature.title }}</h2>
                    <p class="mt-4 text-lg leading-relaxed text-slate-600">{{ feature.description }}</p>
                    <Link :href="route(feature.link.href)" class="vm-link-arrow mt-6">
                        {{ feature.link.label }}
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </ScrollReveal>
            </div>
        </section>

        <!-- Feature grid -->
        <section class="vm-section-mesh relative pt-20 sm:pt-28">
            <PaperBackground />
            <GradientMesh variant="indigo" />

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <ScrollReveal direction="up">
                    <div class="text-center">
                        <h2 class="vm-section-heading">Take your analytics further</h2>
                        <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600">
                            Everything you need to grow your digital presence — without the privacy baggage.
                        </p>
                    </div>
                </ScrollReveal>

                <div class="mt-16">
                    <FeatureGrid :features="gridFeatures" />
                </div>

                <ScrollReveal direction="up" :delay="100">
                    <div class="mt-10 text-center">
                        <Link :href="route('features')" class="vm-link-arrow text-base">
                            View all features
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </ScrollReveal>
            </div>

            <!-- Closing CTA — full-bleed band, no card -->
            <div class="vm-cta-band -mx-px mt-16 sm:mt-20">
                <CloudDecoration position="hero" />
                <div class="relative mx-auto max-w-3xl px-4 py-14 text-center sm:px-6 sm:py-16 lg:px-8">
                    <ScrollReveal direction="up">
                        <span class="vm-cta-eyebrow">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                            Free to start
                        </span>

                        <h2 class="mt-6 font-serif text-3xl font-bold tracking-tight text-warm-800 sm:text-5xl sm:leading-tight">
                            Ready to start
                            <span class="vm-gradient-text">tracking?</span>
                        </h2>

                        <p class="mx-auto mt-5 max-w-md text-base leading-relaxed text-slate-600 sm:text-lg">
                            Create your free account and add honest analytics in under two minutes.
                        </p>

                        <div class="mt-8 flex flex-wrap items-center justify-center gap-2">
                            <span v-for="check in finalChecks" :key="check" class="vm-cta-trust-pill">
                                <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ check }}
                            </span>
                        </div>

                        <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="vm-btn-primary min-w-[11rem] px-10 py-3.5 text-base"
                            >
                                Try for free
                            </Link>
                            <Link :href="route('docs')" class="vm-btn-secondary min-w-[11rem] px-8 py-3.5 text-base">
                                View documentation
                            </Link>
                        </div>
                    </ScrollReveal>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>
