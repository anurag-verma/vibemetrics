<script setup>
import RankList from '@/Components/RankList.vue';
import { ref } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    tabs: {
        type: Array,
        required: true,
    },
});

const activeTab = ref(props.tabs[0]?.id ?? '');

const active = () => props.tabs.find((tab) => tab.id === activeTab.value) ?? props.tabs[0];
</script>

<template>
    <div class="vm-card flex h-full min-h-[280px] flex-col animate-fade-in">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h3 class="vm-panel-title">{{ title }}</h3>
            <div class="flex max-w-full gap-1 overflow-x-auto rounded-lg bg-slate-100 p-1 dark:bg-slate-800">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    class="shrink-0 rounded-md px-2.5 py-1 text-xs font-medium transition"
                    :class="activeTab === tab.id
                        ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white'
                        : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'"
                    @click="activeTab = tab.id"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <div class="flex-1">
            <RankList
                v-if="active()"
                :title="''"
                :items="active().items"
                :label-type="active().labelType ?? 'auto'"
                :empty-text="active().emptyText ?? 'No data available'"
                bare
            />
        </div>
    </div>
</template>
