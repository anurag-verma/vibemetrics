<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SearchableTimezonePicker from '@/Components/SearchableTimezonePicker.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { DATE_RANGE_PRESETS, DEFAULT_DATE_RANGE } from '@/data/dateRanges';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    area: {
        type: String,
        default: 'user',
        validator: (value) => ['user', 'admin'].includes(value),
    },
    timezones: {
        type: Array,
        default: () => [],
    },
    dateRangePresets: {
        type: Object,
        default: () => ({}),
    },
});

const updateRoute = props.area === 'admin' ? 'admin.profile.update' : 'profile.update';

const user = usePage().props.auth.user;
const defaultTimezone = user.timezone || 'UTC';
const defaultDateRange = user.default_date_range || DEFAULT_DATE_RANGE;

const presetOptions = Object.keys(props.dateRangePresets).length > 0
    ? Object.entries(props.dateRangePresets).map(([value, label]) => ({ value, label }))
    : DATE_RANGE_PRESETS.filter((preset) => preset.value !== 'custom');

const form = useForm({
    name: user.name,
    timezone: user.timezone || 'UTC',
    default_date_range: user.default_date_range || DEFAULT_DATE_RANGE,
});

const resetTimezone = () => {
    form.timezone = defaultTimezone;
};

const resetDateRange = () => {
    form.default_date_range = defaultDateRange;
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                Preferences
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-slate-400">
                Analytics dates, default range, and traffic timing use your preferences.
            </p>
        </header>

        <form
            class="mt-6 space-y-6"
            @submit.prevent="form.patch(route(updateRoute))"
        >
            <div>
                <InputLabel for="default_date_range" value="Default date range" />

                <div class="mt-1 flex gap-2">
                    <select
                        id="default_date_range"
                        v-model="form.default_date_range"
                        class="vm-input block w-full"
                    >
                        <option
                            v-for="preset in presetOptions"
                            :key="preset.value"
                            :value="preset.value"
                        >
                            {{ preset.label }}
                        </option>
                    </select>

                    <SecondaryButton type="button" class="shrink-0" @click="resetDateRange">
                        Reset
                    </SecondaryButton>
                </div>

                <InputError class="mt-2" :message="form.errors.default_date_range" />
            </div>

            <div>
                <InputLabel for="timezone" value="Timezone" />

                <div class="mt-1 flex gap-2">
                    <SearchableTimezonePicker
                        id="timezone"
                        v-model="form.timezone"
                        :options="timezones"
                        placeholder="Search timezones…"
                    />

                    <SecondaryButton type="button" class="shrink-0" @click="resetTimezone">
                        Reset
                    </SecondaryButton>
                </div>

                <InputError class="mt-2" :message="form.errors.timezone" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
            </div>
        </form>
    </section>
</template>
