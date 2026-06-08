<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const tabs = [
    {
        id: 'saas',
        slug: 'saas',
        label: 'SaaS',
        title: 'One dashboard for all your sites',
        description: 'Track signups, feature pages, and onboarding funnels across every product you ship — from one privacy-first dashboard.',
        icon: 'rocket',
        color: 'bg-indigo-50 text-indigo-600',
    },
    {
        id: 'ecommerce',
        slug: 'ecommerce',
        label: 'E-commerce',
        title: 'Know what drives purchases',
        description: 'See top product pages, campaign referrers, and UTM performance without cookie banners or consent popups.',
        icon: 'chart',
        color: 'bg-rose-50 text-rose-600',
    },
    {
        id: 'blogs',
        slug: 'blogs',
        label: 'Blogs',
        title: 'Measure content that resonates',
        description: 'Track top posts, referrer sources, and reader trends with a lightweight script that never slows your site.',
        icon: 'page',
        color: 'bg-amber-50 text-amber-600',
    },
    {
        id: 'agencies',
        slug: 'agencies',
        label: 'Agencies',
        title: 'Report across every client',
        description: 'Multi-site dashboards, CSV exports, and clean tenant isolation for professional client reporting.',
        icon: 'sites',
        color: 'bg-violet-50 text-violet-600',
    },
];

const active = ref('saas');
const activeTab = () => tabs.find((t) => t.id === active.value) ?? tabs[0];
</script>

<template>
    <div>
        <div class="flex flex-wrap justify-center gap-2">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                type="button"
                :class="active === tab.id ? 'vm-use-case-tab-active' : ''"
                class="vm-use-case-tab"
                @click="active = tab.id"
            >
                {{ tab.label }}
            </button>
        </div>

        <div class="vm-craft-card-interactive mx-auto mt-8 max-w-xl p-6 transition-all duration-300">
            <div class="flex items-start gap-4">
                <span :class="activeTab().color" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl">
                    <svg v-if="activeTab().icon === 'rocket'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <svg v-else-if="activeTab().icon === 'chart'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <svg v-else-if="activeTab().icon === 'page'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                    </svg>
                </span>
                <div>
                    <h3 class="font-serif text-xl font-bold text-warm-800">{{ activeTab().title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ activeTab().description }}</p>
                    <Link :href="route('use-cases.show', activeTab().slug)" class="vm-link-arrow mt-4">
                        Learn more
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
