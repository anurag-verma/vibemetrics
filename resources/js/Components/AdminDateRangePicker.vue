<script setup>
import DateRangePicker from '@/Components/DateRangePicker.vue';

defineProps({
    dateRange: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <DateRangePicker
        :date-range="dateRange"
        route-name="admin.dashboard"
    />
</template>
