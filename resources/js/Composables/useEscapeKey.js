import { onMounted, onUnmounted, watch } from 'vue';

export function useEscapeKey(isActive, onEscape) {
    const handleKeydown = (event) => {
        if (event.key === 'Escape' && isActive.value) {
            onEscape();
        }
    };

    watch(isActive, (active) => {
        if (active) {
            document.addEventListener('keydown', handleKeydown);
        } else {
            document.removeEventListener('keydown', handleKeydown);
        }
    }, { immediate: true });

    onUnmounted(() => {
        document.removeEventListener('keydown', handleKeydown);
    });
}
