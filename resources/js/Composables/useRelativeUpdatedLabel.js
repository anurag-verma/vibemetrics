import { computed, onMounted, onUnmounted, ref } from 'vue';

export function useRelativeUpdatedLabel(lastUpdated) {
    const now = ref(Date.now());
    let tickInterval = null;

    onMounted(() => {
        tickInterval = setInterval(() => {
            now.value = Date.now();
        }, 20_000);
    });

    onUnmounted(() => {
        clearInterval(tickInterval);
    });

    const lastUpdatedLabel = computed(() => {
        const seconds = Math.floor((now.value - lastUpdated.value.getTime()) / 1000);
        if (seconds < 20) return 'Updated just now';
        if (seconds < 40) return 'Updated 20s ago';
        if (seconds < 60) return 'Updated 40s ago';
        return `Updated ${lastUpdated.value.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' })}`;
    });

    return { lastUpdatedLabel };
}
