export const DATE_RANGE_GROUPS = [
    {
        items: [
            { value: 'today', label: 'Today' },
            { value: 'last_24_hours', label: 'Last 24 hours' },
        ],
    },
    {
        items: [
            { value: 'this_week', label: 'This week' },
            { value: 'last_7_days', label: 'Last 7 days' },
        ],
    },
    {
        items: [
            { value: 'this_month', label: 'This month' },
            { value: 'last_30_days', label: 'Last 30 days' },
            { value: 'last_90_days', label: 'Last 90 days' },
        ],
    },
    {
        items: [
            { value: 'this_year', label: 'This year' },
            { value: 'last_6_months', label: 'Last 6 months' },
            { value: 'last_12_months', label: 'Last 12 months' },
        ],
    },
    {
        items: [
            { value: 'custom', label: 'Custom range' },
        ],
    },
];

export const DATE_RANGE_PRESETS = DATE_RANGE_GROUPS
    .flatMap((group) => group.items);

export const DATE_RANGE_LABELS = Object.fromEntries(
    DATE_RANGE_PRESETS.map((preset) => [preset.value, preset.label]),
);

export const DEFAULT_DATE_RANGE = 'last_30_days';
