import { ref } from 'vue';

const STORAGE_KEY = 'vibemetrics-sidebar-collapsed';

function getStoredCollapsed() {
    return localStorage.getItem(STORAGE_KEY) === '1';
}

export function useSidebar() {
    const collapsed = ref(getStoredCollapsed());

    const toggle = () => {
        collapsed.value = !collapsed.value;
        localStorage.setItem(STORAGE_KEY, collapsed.value ? '1' : '0');
    };

    return { collapsed, toggle };
}
