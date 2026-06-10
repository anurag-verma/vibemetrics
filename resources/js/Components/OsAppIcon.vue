<script setup>
/** OS app-icon tiles (sourced from Umami Analytics, MIT license). */
const icons = {
    ios: '/images/os/ios.png',
    macos: '/images/os/mac-os.png',
    linux: '/images/os/linux.png',
};

defineProps({
    name: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <img
        v-if="icons[name]"
        :src="icons[name]"
        alt=""
        class="analytics-os-app"
    />
</template>
