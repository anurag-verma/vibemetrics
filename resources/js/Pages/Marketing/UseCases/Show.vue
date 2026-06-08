<script setup>
import CloudDecoration from '@/Components/Marketing/CloudDecoration.vue';
import GradientMesh from '@/Components/Marketing/GradientMesh.vue';
import PaperBackground from '@/Components/Marketing/PaperBackground.vue';
import ScrollReveal from '@/Components/Marketing/ScrollReveal.vue';
import { useCases } from '@/data/useCases';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    slug: { type: String, required: true },
});

const useCase = computed(() => useCases.find((item) => item.slug === props.slug));
const otherCases = computed(() => useCases.filter((item) => item.slug !== props.slug));
</script>

<template>
    <Head :title="useCase ? `${useCase.label} — Use Cases` : 'Use Cases'" />

    <MarketingLayout :can-login="canLogin" :can-register="canRegister">
        <template v-if="useCase">
            <section class="vm-section-mesh relative overflow-hidden">
                <PaperBackground />
                <GradientMesh variant="hero" />
                <CloudDecoration position="hero" />

                <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
                    <ScrollReveal direction="up">
                        <Link :href="route('use-cases')" class="vm-link-arrow text-indigo-600">
                            <svg class="h-4 w-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            All use cases
                        </Link>

                        <p class="mt-8 text-sm font-semibold uppercase tracking-wider text-indigo-600">{{ useCase.eyebrow }}</p>
                        <h1 class="mt-4 max-w-3xl font-serif text-4xl font-bold tracking-tight text-warm-800 sm:text-5xl">
                            {{ useCase.title }}
                        </h1>
                        <p class="mt-6 max-w-2xl text-lg leading-relaxed text-slate-600">{{ useCase.description }}</p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <Link v-if="canRegister" :href="route('register')" class="vm-btn-primary px-7 py-3 text-base">
                                Try for free
                            </Link>
                            <Link :href="route('docs')" class="vm-btn-secondary px-7 py-3 text-base">
                                View documentation
                            </Link>
                        </div>
                    </ScrollReveal>
                </div>
            </section>

            <section class="vm-section-mesh-alt relative py-16 sm:py-24">
                <PaperBackground variant="alt" />

                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <ScrollReveal direction="up">
                        <h2 class="vm-section-heading">How VibeMetrics helps</h2>
                        <p class="mt-4 max-w-2xl text-lg text-slate-600">
                            Purpose-built for {{ useCase.label.toLowerCase() }} teams who need clear data without the privacy trade-offs.
                        </p>
                    </ScrollReveal>

                    <div class="mt-12 grid gap-6 sm:grid-cols-2">
                        <ScrollReveal
                            v-for="(highlight, index) in useCase.highlights"
                            :key="highlight.title"
                            direction="up"
                            :delay="index * 80"
                        >
                            <div class="vm-craft-card h-full p-6">
                                <h3 class="font-serif text-lg font-bold text-warm-800">{{ highlight.title }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ highlight.description }}</p>
                            </div>
                        </ScrollReveal>
                    </div>
                </div>
            </section>

            <section class="vm-section-mesh relative py-16 sm:py-20">
                <PaperBackground />
                <GradientMesh variant="indigo" />

                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid items-center gap-12 lg:grid-cols-2">
                        <ScrollReveal direction="left">
                            <h2 class="vm-section-heading">What you can measure</h2>
                            <p class="mt-4 text-lg text-slate-600">
                                Start with pageviews, referrers, and campaigns — then export reports your stakeholders actually understand.
                            </p>
                            <ul class="mt-8 space-y-3">
                                <li
                                    v-for="outcome in useCase.outcomes"
                                    :key="outcome"
                                    class="flex items-center gap-3 rounded-xl border border-warm-200 bg-paper px-4 py-3 text-sm text-warm-800"
                                >
                                    <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ outcome }}
                                </li>
                            </ul>
                        </ScrollReveal>

                        <ScrollReveal direction="right" :delay="100">
                            <div class="vm-craft-card p-8">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Example snapshot</p>
                                <p class="mt-3 font-serif text-2xl font-bold text-indigo-700">24,831</p>
                                <p class="text-sm font-medium text-emerald-600">+12.4% page views vs last period</p>
                                <ul class="mt-6 space-y-2 border-t border-warm-200 pt-6 text-sm">
                                    <li class="flex justify-between text-slate-600"><span>Top page</span><span class="font-semibold text-warm-800">/pricing</span></li>
                                    <li class="flex justify-between text-slate-600"><span>Top referrer</span><span class="font-semibold text-warm-800">google.com</span></li>
                                    <li class="flex justify-between text-slate-600"><span>Live visitors</span><span class="font-semibold text-warm-800">12 online</span></li>
                                </ul>
                            </div>
                        </ScrollReveal>
                    </div>
                </div>
            </section>

            <section class="vm-section-mesh-alt relative border-t border-warm-200/80 py-16 sm:py-20">
                <PaperBackground variant="alt" />

                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <ScrollReveal direction="up">
                        <h2 class="text-center font-serif text-2xl font-bold text-warm-800">Explore other use cases</h2>
                    </ScrollReveal>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3">
                        <ScrollReveal
                            v-for="(item, index) in otherCases"
                            :key="item.slug"
                            direction="up"
                            :delay="index * 60"
                        >
                            <Link
                                :href="route('use-cases.show', item.slug)"
                                class="vm-craft-card-interactive block p-5"
                            >
                                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ item.label }}</p>
                                <p class="mt-2 font-semibold text-warm-800">{{ item.title }}</p>
                            </Link>
                        </ScrollReveal>
                    </div>
                </div>
            </section>
        </template>
    </MarketingLayout>
</template>
