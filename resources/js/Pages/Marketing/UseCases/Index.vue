<script setup>
import CloudDecoration from '@/Components/Marketing/CloudDecoration.vue';
import GradientMesh from '@/Components/Marketing/GradientMesh.vue';
import PaperBackground from '@/Components/Marketing/PaperBackground.vue';
import ScrollReveal from '@/Components/Marketing/ScrollReveal.vue';
import { useCases } from '@/data/useCases';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Use Cases" />

    <MarketingLayout :can-login="canLogin" :can-register="canRegister">
        <section class="vm-section-mesh relative overflow-hidden">
            <PaperBackground />
            <GradientMesh variant="hero" />
            <CloudDecoration position="hero" />

            <div class="mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 sm:py-28 lg:px-8">
                <ScrollReveal direction="up">
                    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Use cases</p>
                    <h1 class="mt-4 font-serif text-4xl font-bold tracking-tight text-warm-800 sm:text-5xl">
                        Analytics for
                        <span class="vm-gradient-text">every team</span>
                    </h1>
                    <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-slate-600">
                        Whether you run a SaaS product, online store, blog, or client portfolio —
                        VibeMetrics gives you privacy-first insights tailored to how you work.
                    </p>
                </ScrollReveal>
            </div>
        </section>

        <section class="vm-section-mesh-alt relative py-16 sm:py-24">
            <PaperBackground variant="alt" />

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 sm:grid-cols-2">
                    <ScrollReveal
                        v-for="(item, index) in useCases"
                        :key="item.slug"
                        direction="up"
                        :delay="index * 80"
                    >
                        <Link
                            :href="route('use-cases.show', item.slug)"
                            class="vm-craft-card-interactive group flex h-full flex-col p-8"
                        >
                            <span :class="item.color" class="flex h-12 w-12 items-center justify-center rounded-xl">
                                <svg v-if="item.icon === 'rocket'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <svg v-else-if="item.icon === 'chart'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <svg v-else-if="item.icon === 'page'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                                </svg>
                            </span>

                            <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ item.eyebrow }}</p>
                            <h2 class="mt-2 font-serif text-2xl font-bold text-warm-800 transition group-hover:text-indigo-700">{{ item.title }}</h2>
                            <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-600">{{ item.summary }}</p>

                            <span class="vm-link-arrow mt-6">
                                Learn more
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </Link>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <section class="vm-section-mesh relative py-16 sm:py-20">
            <PaperBackground />
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
                <ScrollReveal direction="up">
                    <h2 class="vm-section-heading">Ready to get started?</h2>
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
