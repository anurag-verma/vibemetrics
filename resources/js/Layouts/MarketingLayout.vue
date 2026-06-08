<script setup>
import Logo from '@/Components/Logo.vue';
import { useCurrentYear } from '@/Composables/useCurrentYear';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const currentYear = useCurrentYear();

defineProps({
    canLogin: { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-paper text-warm-800">
        <header class="sticky top-0 z-50 border-b border-warm-200/80 bg-paper/90 shadow-sm backdrop-blur-lg">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Logo size="nav" />

                <nav class="hidden items-center gap-8 md:flex">
                    <Link :href="route('features')" class="text-sm font-medium text-slate-600 underline-offset-4 transition duration-300 hover:text-warm-800 hover:underline">Features</Link>
                    <Link :href="route('use-cases')" class="text-sm font-medium text-slate-600 underline-offset-4 transition duration-300 hover:text-warm-800 hover:underline">Use Cases</Link>
                    <Link :href="route('pricing')" class="text-sm font-medium text-slate-600 underline-offset-4 transition duration-300 hover:text-warm-800 hover:underline">Pricing</Link>
                    <Link :href="route('docs')" class="text-sm font-medium text-slate-600 underline-offset-4 transition duration-300 hover:text-warm-800 hover:underline">Docs</Link>
                </nav>

                <div class="flex items-center gap-3">
                    <template v-if="user">
                        <Link :href="route('dashboard')" class="vm-btn-primary text-sm">Dashboard</Link>
                    </template>
                    <template v-else>
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="text-sm font-medium text-slate-600 transition hover:text-warm-800"
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
                        <p class="mt-4 text-sm text-slate-500">Product news and analytics tips. No spam.</p>
                        <form class="mt-4 flex gap-2" @submit.prevent>
                            <input
                                type="email"
                                placeholder="you@company.com"
                                class="vm-marketing-input flex-1 rounded-full"
                                aria-label="Email for newsletter"
                            />
                            <button type="submit" class="vm-btn-primary shrink-0 text-sm">
                                Subscribe
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-8 border-t border-warm-200 pt-6 text-center text-xs text-slate-400">
                    &copy; {{ currentYear }} VibeMetrics. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>
