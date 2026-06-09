import { onUnmounted, watch } from 'vue';

let lockCount = 0;
let previousOverflow = '';

function lockBody() {
    if (lockCount === 0) {
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
    }

    lockCount += 1;
}

function unlockBody() {
    if (lockCount <= 0) {
        return;
    }

    lockCount -= 1;

    if (lockCount === 0) {
        document.body.style.overflow = previousOverflow;
    }
}

export function useBodyScrollLock(isOpen) {
    watch(
        isOpen,
        (open) => {
            if (open) {
                lockBody();
            } else {
                unlockBody();
            }
        },
        { immediate: true },
    );

    onUnmounted(() => {
        if (isOpen.value) {
            unlockBody();
        }
    });
}
