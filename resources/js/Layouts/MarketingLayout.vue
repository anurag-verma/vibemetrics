<script setup>
import AppVersion from '@/Components/AppVersion.vue';
import Logo from '@/Components/Logo.vue';
import { useBodyScrollLock } from '@/Composables/useBodyScrollLock';
import { useCurrentYear } from '@/Composables/useCurrentYear';
import { useEscapeKey } from '@/Composables/useEscapeKey';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const currentYear = useCurrentYear();

const props = defineProps({
    canLogin: { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const mobileMenuOpen = ref(false);

const navLinks = [
    { href: 'features', label: 'Features' },
    { href: 'use-cases', label: 'Use Cases' },
    { href: 'pricing', label: 'Pricing' },
    { href: 'docs', label: 'Docs' },
];

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

useBodyScrollLock(mobileMenuOpen);
useEscapeKey(mobileMenuOpen, closeMobileMenu);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-paper text-warm-800">
        <header class="sticky top-0 z-50 border-b border-warm-200/80 bg-paper/90 shadow-sm backdrop-blur-lg">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg p-2 text-slate-600 transition hover:bg-warm-100 md:hidden"
                        :aria-expanded="mobileMenuOpen"
                        aria-controls="marketing-mobile-menu"
                        aria-label="Open navigation menu"
                        @click="mobileMenuOpen = true"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <Logo size="nav" />
                </div>

                <nav class="hidden items-center gap-8 md:flex">
                    <Link
                        v-for="link in navLinks"
                        :key="link.href"
                        :href="route(link.href)"
                        class="text-sm font-medium text-slate-600 underline-offset-4 transition duration-300 hover:text-warm-800 hover:underline"
                    >
                        {{ link.label }}
                    </Link>
                </nav>

                <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                    <template v-if="user">
                        <Link :href="route('dashboard')" class="vm-btn-primary text-sm">Dashboard</Link>
                    </template>
                    <template v-else>
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="hidden text-sm font-medium text-slate-600 transition hover:text-warm-800 sm:inline"
                        >
                            Log in
                        </Link>
                        <Link v-if="canRegister" :href="route('register')" class="vm-btn-primary text-sm">
                            Get started
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <div
            v-if="mobileMenuOpen"
            class="fixed inset-0 z-[60] bg-black/40 md:hidden"
            aria-hidden="true"
            @click="closeMobileMenu"
        />

        <div
            id="marketing-mobile-menu"
            class="fixed inset-y-0 left-0 z-[70] flex w-[min(100%,18rem)] flex-col border-r border-warm-200 bg-paper shadow-xl transition-transform duration-200 md:hidden"
            :class="mobileMenuOpen ? 'translate-x-0' : 'pointer-events-none -translate-x-full'"
            :aria-hidden="!mobileMenuOpen"
            role="dialog"
            aria-modal="true"
            aria-label="Navigation menu"
        >
            <div class="flex h-16 shrink-0 items-center justify-between border-b border-warm-200 px-4">
                <Logo size="nav" />
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-600 transition hover:bg-warm-100"
                    aria-label="Close navigation menu"
                    @click="closeMobileMenu"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto p-4">
                <Link
                    v-for="link in navLinks"
                    :key="link.href"
                    :href="route(link.href)"
                    class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-warm-100 hover:text-warm-800"
                    @click="closeMobileMenu"
                >
                    {{ link.label }}
                </Link>
            </nav>

            <div class="shrink-0 space-y-2 border-t border-warm-200 p-4">
                <template v-if="user">
                    <Link
                        :href="route('dashboard')"
                        class="vm-btn-primary block w-full text-center text-sm"
                        @click="closeMobileMenu"
                    >
                        Dashboard
                    </Link>
                </template>
                <template v-else>
                    <Link
                        v-if="canLogin"
                        :href="route('login')"
                        class="block rounded-lg px-3 py-2.5 text-center text-sm font-medium text-slate-700 transition hover:bg-warm-100"
                        @click="closeMobileMenu"
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="vm-btn-primary block w-full text-center text-sm"
                        @click="closeMobileMenu"
                    >
                        Get started
                    </Link>
                </template>
            </div>
        </div>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t border-warm-200 bg-warm-100">
            <div class="mx-auto max-w-7xl px-4 pt-12 pb-6 sm:px-6 lg:px-8">
                <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                    <div class="lg:col-span-1">
                        <Logo size="sm" />
                        <p class="mt-4 max-w-xs text-sm leading-relaxed text-slate-500">
                            Privacy-first analytics for modern teams. No cookies, no personal data stored — just clean insights you can trust.
                        </p>
                    </div>

                    <div>
                        <h4 class="font-serif text-sm font-semibold text-warm-800">Product</h4>
                        <ul class="mt-4 space-y-3 text-sm text-slate-500">
                            <li><Link :href="route('features')" class="underline-offset-4 transition hover:text-warm-800 hover:underline">Features</Link></li>
                            <li><Link :href="route('use-cases')" class="underline-offset-4 transition hover:text-warm-800 hover:underline">Use Cases</Link></li>
                            <li><Link :href="route('pricing')" class="underline-offset-4 transition hover:text-warm-800 hover:underline">Pricing</Link></li>
                            <li><Link :href="route('docs')" class="underline-offset-4 transition hover:text-warm-800 hover:underline">Documentation</Link></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-serif text-sm font-semibold text-warm-800">Legal</h4>
                        <ul class="mt-4 space-y-3 text-sm text-slate-500">
                            <li><Link :href="route('privacy')" class="underline-offset-4 transition hover:text-warm-800 hover:underline">Privacy Policy</Link></li>
                            <li><Link :href="route('terms')" class="underline-offset-4 transition hover:text-warm-800 hover:underline">Terms of Service</Link></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-serif text-sm font-semibold text-warm-800">Stay updated</h4>
                        <p class="mt-4 text-sm text-slate-500">Product news and analytics tips. Newsletter coming soon.</p>
                    </div>
                </div>

                <div class="mt-8 border-t border-warm-200 pt-6 text-center text-xs text-slate-400">
                    &copy; {{ currentYear }} VibeMetrics. All rights reserved.<AppVersion variant="inline" />
                </div>
            </div>
        </footer>
    </div>
</template>
