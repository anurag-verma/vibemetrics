import { nextTick, onUnmounted, ref, watch } from 'vue';

const VIEWPORT_MARGIN = 8;
const PANEL_GAP = 4;

export function useViewportDropdownPosition(root, panel, open, options = {}) {
    const panelStyle = ref({});
    const preferredWidth = options.width ?? 256;

    const updatePosition = async () => {
        if (!open.value || !root.value) {
            panelStyle.value = {};
            return;
        }

        await nextTick();

        const trigger = root.value;
        const rect = trigger.getBoundingClientRect();
        // Teleported panels are full-width until styled; never measure offsetWidth unless matching trigger.
        const naturalWidth = options.matchTriggerWidth
            ? rect.width
            : preferredWidth;
        const viewportWidth = window.innerWidth;
        const width = Math.min(Math.max(naturalWidth, options.minWidth ?? 0), viewportWidth - VIEWPORT_MARGIN * 2);

        let left = rect.right - width;

        if (left + width > viewportWidth - VIEWPORT_MARGIN) {
            left = viewportWidth - width - VIEWPORT_MARGIN;
        }

        if (left < VIEWPORT_MARGIN) {
            left = VIEWPORT_MARGIN;
        }

        panelStyle.value = {
            position: 'fixed',
            top: `${rect.bottom + PANEL_GAP}px`,
            left: `${left}px`,
            width: `${width}px`,
            zIndex: 60,
        };
    };

    const onReposition = () => {
        if (open.value) {
            updatePosition();
        }
    };

    watch(open, (isOpen) => {
        if (isOpen) {
            updatePosition();
            window.addEventListener('resize', onReposition);
            window.addEventListener('scroll', onReposition, true);
        } else {
            window.removeEventListener('resize', onReposition);
            window.removeEventListener('scroll', onReposition, true);
            panelStyle.value = {};
        }
    });

    onUnmounted(() => {
        window.removeEventListener('resize', onReposition);
        window.removeEventListener('scroll', onReposition, true);
    });

    return { panelStyle, updatePosition };
}

export function isClickInsideDropdown(event, ...elements) {
    const target = event.target;

    return elements.some((element) => {
        const node = element?.value ?? element;

        return node && typeof node.contains === 'function' && node.contains(target);
    });
}
