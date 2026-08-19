<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import Button from 'primevue/button';

const props = defineProps({
    filters: { type: Object, default: () => ({}) },
    filterOptions: { type: Object, default: () => ({}) },
});

const userId = ref(props.filters.user_id ? Number(props.filters.user_id) : null);
const event = ref(props.filters.event ?? null);
const auditableType = ref(props.filters.auditable_type ?? null);
const date = ref(props.filters.date ? new Date(`${props.filters.date}T12:00:00`) : null);

const userOptions = computed(() =>
    (props.filterOptions.users ?? []).map((user) => ({
        value: user.id,
        label: user.name,
    })),
);

const eventOptions = computed(() =>
    (props.filterOptions.events ?? []).map((option) => ({
        value: option.value,
        label: trans(option.label_key),
    })),
);

const tableOptions = computed(() =>
    (props.filterOptions.tables ?? []).map((option) => ({
        value: option.value,
        label: trans(option.label_key),
    })),
);

const hasActiveFilters = computed(() =>
    Boolean(userId.value || event.value || auditableType.value || date.value),
);

watch([userId, event, auditableType, date], () => applyFilters());

function formatDateParam(value) {
    if (!value) {
        return null;
    }

    const year = value.getFullYear();
    const month = String(value.getMonth() + 1).padStart(2, '0');
    const day = String(value.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function applyFilters() {
    const params = {};

    if (userId.value) params.user_id = userId.value;
    if (event.value) params.event = event.value;
    if (auditableType.value) params.auditable_type = auditableType.value;

    const dateValue = formatDateParam(date.value);
    if (dateValue) params.date = dateValue;

    router.get(route('audit.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function clearFilters() {
    userId.value = null;
    event.value = null;
    auditableType.value = null;
    date.value = null;

    router.get(route('audit.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}
</script>

<template>
    <div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <Select
                v-model="userId"
                :options="userOptions"
                optionLabel="label"
                optionValue="value"
                :placeholder="trans('audit.filters.user')"
                showClear
                filter
                class="w-full"
            />

            <Select
                v-model="event"
                :options="eventOptions"
                optionLabel="label"
                optionValue="value"
                :placeholder="trans('audit.filters.event')"
                showClear
                class="w-full"
            />

            <DatePicker
                v-model="date"
                :placeholder="trans('audit.filters.date')"
                dateFormat="dd/mm/yy"
                showIcon
                showClear
                class="w-full"
            />

            <Select
                v-model="auditableType"
                :options="tableOptions"
                optionLabel="label"
                optionValue="value"
                :placeholder="trans('audit.filters.table')"
                showClear
                class="w-full"
            />

            <div class="flex items-center">
                <Button
                    v-if="hasActiveFilters"
                    :label="trans('common.clear')"
                    icon="pi pi-filter-slash"
                    outlined
                    severity="secondary"
                    class="w-full"
                    @click="clearFilters"
                />
            </div>
        </div>
    </div>
</template>
