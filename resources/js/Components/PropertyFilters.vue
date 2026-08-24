<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { FilterX } from '@lucide/vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import AppSelect from '@/Components/AppSelect.vue';
import FilterPanel from '@/Components/FilterPanel.vue';
import { useFilterSyncGuard } from '@/composables/useFilterSyncGuard';
import {
    blockDisallowedAddressBeforeInput,
    blockDisallowedAddressKey,
    blockNonDigitBeforeInput,
    blockNonDigitKey,
    formatAddressInput,
    stripNonDigits,
} from '@/utils/formatting';

const props = defineProps({
    filters: { type: Object, default: () => ({}) },
    peopleOptions: { type: Array, default: () => [] },
});

const { runSyncedFromProps, shouldSkipFilterApply } = useFilterSyncGuard();

const id = ref(props.filters.id ? String(props.filters.id) : '');
const type = ref(props.filters.type ?? null);
const street = ref(props.filters.street ?? '');
const number = ref(props.filters.number ? String(props.filters.number) : '');
const neighborhood = ref(props.filters.neighborhood ?? '');
const personId = ref(props.filters.person_id ? Number(props.filters.person_id) : null);
const status = ref(props.filters.status ?? null);

let debounceTimer = null;

const typeOptions = computed(() => [
    { value: 'land', label: trans('properties.types.land') },
    { value: 'house', label: trans('properties.types.house') },
    { value: 'apartment', label: trans('properties.types.apartment') },
]);

const statusOptions = computed(() => [
    { value: 'active', label: trans('properties.statuses.active') },
    { value: 'inactive', label: trans('properties.statuses.inactive') },
]);

const hasActiveFilters = computed(() =>
    Boolean(
        id.value
        || type.value
        || street.value
        || number.value
        || neighborhood.value
        || personId.value
        || status.value,
    ),
);

watch(
    () => props.filters,
    (filters) => {
        runSyncedFromProps(() => {
            id.value = filters.id ? String(filters.id) : '';
            type.value = filters.type ?? null;
            street.value = filters.street ?? '';
            number.value = filters.number ? String(filters.number) : '';
            neighborhood.value = filters.neighborhood ?? '';
            personId.value = filters.person_id ? Number(filters.person_id) : null;
            status.value = filters.status ?? null;
        });
    },
    { deep: true },
);

watch([id, street, number, neighborhood], () => {
    if (shouldSkipFilterApply()) {
        return;
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 220);
});

watch([type, personId, status], () => {
    if (shouldSkipFilterApply()) {
        return;
    }

    applyFilters();
});

function syncMasked(fieldRef, formatter, value) {
    const formatted = formatter(value);
    if (formatted === fieldRef.value) {
        fieldRef.value = `${formatted}\u200b`;
        queueMicrotask(() => {
            fieldRef.value = formatted;
        });
        return;
    }
    fieldRef.value = formatted;
}

function onIdInput(value) {
    syncMasked(id, (v) => stripNonDigits(v), value);
}

function onNumberInput(value) {
    syncMasked(number, (v) => stripNonDigits(v), value);
}

function onStreetInput(value) {
    syncMasked(street, formatAddressInput, value);
}

function onNeighborhoodInput(value) {
    syncMasked(neighborhood, formatAddressInput, value);
}

function applyFilters() {
    const params = {};

    const idValue = stripNonDigits(id.value);
    if (idValue) params.id = idValue;

    if (type.value) params.type = type.value;

    const streetValue = formatAddressInput(street.value).trim();
    if (streetValue) params.street = streetValue;

    const numberValue = stripNonDigits(number.value);
    if (numberValue) params.number = numberValue;

    const neighborhoodValue = formatAddressInput(neighborhood.value).trim();
    if (neighborhoodValue) params.neighborhood = neighborhoodValue;

    if (personId.value) params.person_id = personId.value;
    if (status.value) params.status = status.value;

    router.get(route('properties.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function clearFilters() {
    id.value = '';
    type.value = null;
    street.value = '';
    number.value = '';
    neighborhood.value = '';
    personId.value = null;
    status.value = null;

    router.get(route('properties.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}
</script>

<template>
    <FilterPanel :title="trans('properties.filters.heading')">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div
                @keydown.capture="blockNonDigitKey"
                @beforeinput.capture="blockNonDigitBeforeInput"
            >
                <Input
                    :model-value="id"
                    :placeholder="trans('properties.filters.municipal_registration')"
                    class="w-full"
                    inputmode="numeric"
                    @update:model-value="onIdInput"
                />
            </div>

            <AppSelect
                v-model="type"
                :options="typeOptions"
                :placeholder="trans('properties.filters.type')"
                show-clear
                class="w-full"
            />

            <div
                @keydown.capture="blockDisallowedAddressKey"
                @beforeinput.capture="blockDisallowedAddressBeforeInput"
            >
                <Input
                    :model-value="street"
                    :placeholder="trans('properties.filters.street')"
                    class="w-full"
                    @update:model-value="onStreetInput"
                />
            </div>

            <div
                @keydown.capture="blockNonDigitKey"
                @beforeinput.capture="blockNonDigitBeforeInput"
            >
                <Input
                    :model-value="number"
                    :placeholder="trans('properties.filters.number')"
                    class="w-full"
                    inputmode="numeric"
                    @update:model-value="onNumberInput"
                />
            </div>

            <div
                @keydown.capture="blockDisallowedAddressKey"
                @beforeinput.capture="blockDisallowedAddressBeforeInput"
            >
                <Input
                    :model-value="neighborhood"
                    :placeholder="trans('properties.filters.neighborhood')"
                    class="w-full"
                    @update:model-value="onNeighborhoodInput"
                />
            </div>

            <AppSelect
                v-model="personId"
                :options="peopleOptions"
                :placeholder="trans('properties.filters.owner')"
                filter
                show-clear
                class="w-full"
            />

            <AppSelect
                v-model="status"
                :options="statusOptions"
                :placeholder="trans('properties.filters.status')"
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
