<script setup>
import CloudDecoration from '@/Components/Marketing/CloudDecoration.vue';
import FeatureGrid from '@/Components/Marketing/FeatureGrid.vue';
import GradientMesh from '@/Components/Marketing/GradientMesh.vue';
import PaperBackground from '@/Components/Marketing/PaperBackground.vue';
import ScrollReveal from '@/Components/Marketing/ScrollReveal.vue';
import { gridFeatures, storyFeatures } from '@/data/features';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Features" />

    <MarketingLayout :can-login="canLogin" :can-register="canRegister">
        <section class="vm-section-mesh relative overflow-hidden">
            <PaperBackground />
            <GradientMesh variant="hero" />
            <CloudDecoration position="hero" />

            <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-24 lg:px-8">
                <ScrollReveal direction="up">
                    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Features</p>
                    <h1 class="mt-4 font-serif text-4xl font-bold tracking-tight text-warm-800 sm:text-5xl">
                        Take your analytics
                        <span class="vm-gradient-text">further</span>
                    </h1>
                    <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-slate-600">
                        Everything you need to grow your digital presence — lightweight tracking,
                        clear dashboards, and privacy by design.
                    </p>
                </ScrollReveal>
            </div>
        </section>

        <section class="vm-section-mesh-alt relative py-16 sm:py-24">
            <PaperBackground variant="alt" />

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 sm:grid-cols-2">
                    <ScrollReveal
                        v-for="(feature, index) in storyFeatures"
                        :key="feature.title"
                        direction="up"
                        :delay="index * 80"
                    >
                        <div class="vm-craft-card h-full p-6 sm:p-8">
                            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ feature.eyebrow }}</p>
                            <h2 class="mt-3 font-serif text-xl font-bold text-warm-800">{{ feature.title }}</h2>
                            <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ feature.description }}</p>
                        </div>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <section class="vm-section-mesh relative py-16 sm:py-24">
            <PaperBackground />
            <GradientMesh variant="indigo" />

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <ScrollReveal direction="up">
                    <div class="text-center">
                        <h2 class="vm-section-heading">Everything included</h2>
                        <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600">
                            All features are available on the free plan — no upgrades required.
                        </p>
                    </div>
                </ScrollReveal>

                <div class="mt-16">
                    <FeatureGrid :features="gridFeatures" />
                </div>
            </div>
        </section>

        <section class="vm-section-mesh-alt relative py-16 sm:py-20">
            <PaperBackground variant="alt" />
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
                <ScrollReveal direction="up">
                    <h2 class="vm-section-heading">Ready to start tracking?</h2>
                    <p class="mt-4 text-lg text-slate-600">Create your free account and add honest analytics in under two minutes.</p>
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
