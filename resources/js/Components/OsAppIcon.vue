<script setup>
import { siApple } from 'simple-icons';
import { useId } from 'vue';

defineProps({
    name: {
        type: String,
        required: true,
    },
});

const applePath = siApple.path;
const macosGradientId = `macos-finder-bg-${useId()}`;
</script>

<template>
    <!-- iOS: Apple logo on black app-icon tile -->
    <svg
        v-if="name === 'ios'"
        viewBox="0 0 24 24"
        class="analytics-os-app"
        aria-hidden="true"
    >
        <rect width="24" height="24" rx="5.5" fill="#000000" />
        <g transform="translate(12 12.3) scale(0.5) translate(-12 -12)">
            <path :d="applePath" fill="#ffffff" />
        </g>
    </svg>

    <!-- macOS: Finder-style face on blue app-icon tile -->
    <svg
        v-else-if="name === 'macos'"
        viewBox="0 0 24 24"
        class="analytics-os-app"
        aria-hidden="true"
    >
        <defs>
            <linearGradient :id="macosGradientId" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#5EB0FF" />
                <stop offset="100%" stop-color="#1E88F7" />
            </linearGradient>
        </defs>
        <rect width="24" height="24" rx="5.5" :fill="`url(#${macosGradientId})`" />
        <ellipse cx="12" cy="13.2" rx="6.2" ry="7.1" fill="#F5F5F7" />
        <ellipse cx="9.35" cy="11.8" rx="1.05" ry="1.45" fill="#1D1D1F" />
        <ellipse cx="14.65" cy="11.8" rx="1.05" ry="1.45" fill="#1D1D1F" />
        <path
            d="M9.1 15.1c.9 1.35 2.2 2 2.9 2s2-.65 2.9-2"
            fill="none"
            stroke="#1D1D1F"
            stroke-linecap="round"
            stroke-width="1.1"
        />
        <path
            d="M12 5.8c-1.5 0-2.7.55-2.7 1.25v.55c0 .35.55.55 1.2.55h3c.65 0 1.2-.2 1.2-.55v-.55c0-.7-1.2-1.25-2.7-1.25z"
            fill="#E8E8ED"
        />
    </svg>

    <!-- Linux: Tux on dark tile (matches app-icon visual weight) -->
    <svg
        v-else-if="name === 'linux'"
        viewBox="0 0 24 24"
        class="analytics-os-app"
        aria-hidden="true"
    >
        <rect width="24" height="24" rx="5.5" fill="#1C1C1E" />
        <!-- Body -->
        <ellipse cx="12" cy="14.8" rx="5.6" ry="6.2" fill="#FFFFFF" />
        <ellipse cx="12" cy="14.5" rx="4.8" ry="5.4" fill="#F2F2F2" />
        <!-- Belly -->
        <ellipse cx="12" cy="15.8" rx="3.1" ry="3.6" fill="#FFFFFF" />
        <!-- Head -->
        <circle cx="12" cy="8.2" r="4.1" fill="#2D2D2D" />
        <circle cx="12" cy="8.5" r="3.5" fill="#1A1A1A" />
        <!-- Eyes -->
        <ellipse cx="10.4" cy="8.1" rx="1.15" ry="1.45" fill="#FFFFFF" />
        <ellipse cx="13.6" cy="8.1" rx="1.15" ry="1.45" fill="#FFFFFF" />
        <circle cx="10.55" cy="8.35" r=".55" fill="#111111" />
        <circle cx="13.75" cy="8.35" r=".55" fill="#111111" />
        <!-- Beak -->
        <path d="M12 9.5 10.8 10.6h2.4L12 9.5Z" fill="#F59E0B" />
        <!-- Wings -->
        <ellipse cx="7.1" cy="14.2" rx="1.5" ry="2.4" fill="#2D2D2D" transform="rotate(-18 7.1 14.2)" />
        <ellipse cx="16.9" cy="14.2" rx="1.5" ry="2.4" fill="#2D2D2D" transform="rotate(18 16.9 14.2)" />
        <!-- Feet -->
        <ellipse cx="9.6" cy="19.4" rx="1.7" ry=".85" fill="#F59E0B" />
        <ellipse cx="14.4" cy="19.4" rx="1.7" ry=".85" fill="#F59E0B" />
    </svg>
</template>
