<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { FilterX } from '@lucide/vue';
import { Button } from '@/Components/ui/button';
import AppSelect from '@/Components/AppSelect.vue';
import DatePickerField from '@/Components/DatePickerField.vue';
import FilterPanel from '@/Components/FilterPanel.vue';
import { useFilterSyncGuard } from '@/composables/useFilterSyncGuard';

const props = defineProps({
    filters: { type: Object, default: () => ({}) },
    filterOptions: { type: Object, default: () => ({}) },
});

const { runSyncedFromProps, shouldSkipFilterApply } = useFilterSyncGuard();

const userId = ref(props.filters.user_id ? Number(props.filters.user_id) : null);
const event = ref(props.filters.event ?? null);
const auditableType = ref(props.filters.auditable_type ?? null);
const date = ref(props.filters.date ? new Date(`${props.filters.date}T12:00:00`) : null);

let debounceTimer = null;

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

watch(
    () => props.filters,
    (filters) => {
        runSyncedFromProps(() => {
            userId.value = filters.user_id ? Number(filters.user_id) : null;
            event.value = filters.event ?? null;
            auditableType.value = filters.auditable_type ?? null;
            date.value = filters.date ? new Date(`${filters.date}T12:00:00`) : null;
        });
    },
    { deep: true },
);

watch([userId, event, auditableType], () => {
    if (shouldSkipFilterApply()) {
        return;
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 200);
});

function formatDateParam(value) {
    if (!value) {
        return null;
    }

    const year = value.getFullYear();
    const month = String(value.getMonth() + 1).padStart(2, '0');
    const day = String(value.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function buildParams() {
    const params = {};

    if (userId.value) params.user_id = userId.value;
    if (event.value) params.event = event.value;
    if (auditableType.value) params.auditable_type = auditableType.value;

    const dateValue = formatDateParam(date.value);
    if (dateValue) params.date = dateValue;

    return params;
}

function paramsMatchCurrentQuery(params) {
    const current = new URLSearchParams(window.location.search);

    for (const [key, value] of Object.entries(params)) {
        if (current.get(key) !== String(value)) {
            return false;
        }
    }

    for (const key of ['user_id', 'event', 'auditable_type', 'date']) {
        if (!(key in params) && current.has(key)) {
            return false;
        }
    }

    return true;
}

function applyFilters() {
    const params = buildParams();

    if (paramsMatchCurrentQuery(params)) {
        return;
    }

    router.get(route('audit.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function onDateFilterChange() {
    if (shouldSkipFilterApply()) {
        return;
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 200);
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
    <FilterPanel :title="trans('audit.filters.heading')">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <AppSelect
                v-model="userId"
                :options="userOptions"
                :placeholder="trans('audit.filters.user')"
                show-clear
                filter
                class="w-full"
            />

            <AppSelect
                v-model="event"
                :options="eventOptions"
                :placeholder="trans('audit.filters.event')"
                show-clear
                class="w-full"
            />

            <DatePickerField
                v-model="date"
                :placeholder="trans('audit.filters.date')"
                show-clear
                class="w-full"
                @date-select="onDateFilterChange"
                @clear-click="onDateFilterChange"
            />

            <AppSelect
                v-model="auditableType"
                :options="tableOptions"
                :placeholder="trans('audit.filters.table')"
                show-clear
                class="w-full"
            />
        </div>

        <template
            v-if="hasActiveFilters"
            #actions
        >
            <Button
                variant="outline"
                class="w-full sm:w-auto"
                @click="clearFilters"
            >
                <FilterX class="size-4" />
                {{ trans('common.clear') }}
            </Button>
        </template>
    </FilterPanel>
</template>
