<script setup>
import { getStoredTheme, setStoredTheme } from '@/Composables/useTheme';
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    email: { type: String, default: '' },
    variant: {
        type: String,
        default: 'user',
        validator: (value) => ['user', 'admin'].includes(value),
    },
    darkSidebar: { type: Boolean, default: false },
    collapsed: { type: Boolean, default: false },
});

const open = ref(false);
const themeOpen = ref(false);
const menuRef = ref(null);
const currentTheme = ref(getStoredTheme());
const canHover = ref(false);

const themeModes = [
    { value: 'system', label: 'System' },
    { value: 'light', label: 'Light' },
    { value: 'dark', label: 'Dark' },
];

const isAdmin = computed(() => props.variant === 'admin');

const profileRoute = computed(() =>
    isAdmin.value ? 'admin.profile.edit' : 'profile.edit',
);

const triggerClass = computed(() =>
    props.darkSidebar
        ? 'text-slate-300 hover:bg-slate-800'
        : 'text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800',
);

const menuClass = computed(() =>
    props.darkSidebar
        ? 'border-slate-700 bg-slate-800'
        : 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800',
);

const itemClass = computed(() =>
    props.darkSidebar
        ? 'text-slate-300 hover:bg-slate-700'
        : 'text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700',
);

const dividerClass = computed(() =>
    props.darkSidebar ? 'border-slate-700' : 'border-slate-200 dark:border-slate-700',
);

const menuPositionClass = computed(() =>
    props.collapsed
        ? 'absolute bottom-full left-0 z-50 mb-2 w-full overflow-visible lg:bottom-0 lg:left-full lg:mb-0 lg:ml-2 lg:w-52'
        : 'absolute bottom-full left-0 z-50 mb-2 w-full overflow-visible',
);

const close = () => {
    open.value = false;
    themeOpen.value = false;
};

const toggle = () => {
    open.value = !open.value;
    if (!open.value) {
        themeOpen.value = false;
    }
};

const toggleTheme = () => {
    themeOpen.value = !themeOpen.value;
};

const openThemeOnHover = () => {
    if (canHover.value) {
        themeOpen.value = true;
    }
};

const closeThemeOnLeave = () => {
    if (canHover.value) {
        themeOpen.value = false;
    }
};

const selectTheme = (mode) => {
    currentTheme.value = mode;
    setStoredTheme(mode);
    themeOpen.value = false;
};

const onClickOutside = (event) => {
    if (menuRef.value && !menuRef.value.contains(event.target)) {
        close();
    }
};

onMounted(() => {
    document.addEventListener('click', onClickOutside);
    canHover.value = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
});

onUnmounted(() => document.removeEventListener('click', onClickOutside));
</script>

<template>
    <div ref="menuRef" class="relative">
        <div
            v-if="open"
            class="overflow-visible rounded-lg border shadow-lg"
            :class="[menuPositionClass, menuClass]"
        >
            <Link
                :href="route(profileRoute)"
                class="flex w-full items-center gap-2 px-3 py-2.5 text-sm transition"
                :class="itemClass"
                @click="close"
            >
                <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Profile
            </Link>

            <div
                class="relative"
                @mouseenter="openThemeOnHover"
                @mouseleave="closeThemeOnLeave"
            >
                <button
                    type="button"
                    class="flex w-full items-center gap-2 px-3 py-2.5 text-sm transition"
                    :class="[itemClass, themeOpen ? (darkSidebar ? 'bg-slate-700' : 'bg-slate-50 dark:bg-slate-700') : '']"
                    aria-haspopup="menu"
                    :aria-expanded="themeOpen"
                    aria-label="Theme"
                    @click.stop="toggleTheme"
                >
                    <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="flex-1 text-left">Theme</span>
                    <svg class="h-3.5 w-3.5 shrink-0 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <div
                    v-show="themeOpen"
                    class="absolute top-0 z-[60] right-full mr-1 pr-1 sm:right-auto sm:mr-0 sm:left-full sm:pl-1 sm:pr-0"
                >
                    <div
                        class="min-w-[9rem] overflow-hidden rounded-lg border shadow-lg"
                        :class="menuClass"
                    >
                        <button
                            v-for="mode in themeModes"
                            :key="mode.value"
                            type="button"
                            class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm transition"
                            :class="[
                                itemClass,
                                currentTheme === mode.value ? 'text-indigo-600 dark:text-indigo-400' : '',
                            ]"
                            @click="selectTheme(mode.value)"
                        >
                            {{ mode.label }}
                            <svg
                                v-if="currentTheme === mode.value"
                                class="h-4 w-4 shrink-0"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <Link
                v-if="isAdmin"
                :href="route('admin.settings.index')"
                class="flex w-full items-center gap-2 px-3 py-2.5 text-sm transition"
                :class="itemClass"
                @click="close"
            >
                <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
            </Link>

            <div class="border-t" :class="dividerClass">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center gap-2 px-3 py-2.5 text-sm transition"
                    :class="itemClass"
                    @click="close"
                >
                    <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </Link>
            </div>
        </div>

        <button
            type="button"
            class="flex w-full items-center rounded-lg py-2 text-left text-sm font-medium transition"
            :class="[triggerClass, collapsed ? 'gap-2.5 px-2 lg:justify-center lg:gap-0 lg:px-1' : 'gap-2.5 px-2']"
            :title="collapsed ? email : undefined"
            :aria-expanded="open"
            aria-haspopup="menu"
            aria-label="Account menu"
            @click.stop="toggle"
        >
            <span
                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                :class="darkSidebar ? 'bg-slate-800 text-slate-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </span>
            <span class="min-w-0 flex-1 truncate" :class="collapsed ? 'lg:hidden' : ''">{{ email }}</span>
        </button>
    </div>
</template>
