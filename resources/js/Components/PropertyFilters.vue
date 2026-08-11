<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Button from 'primevue/button';
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

watch([id, street, number, neighborhood], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 220);
});

watch([type, personId, status], () => applyFilters());

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
    <div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div
                @keydown.capture="blockNonDigitKey"
                @beforeinput.capture="blockNonDigitBeforeInput"
            >
                <InputText
                    :modelValue="id"
                    :placeholder="trans('properties.filters.municipal_registration')"
                    class="w-full font-mono"
                    inputmode="numeric"
                    @update:model-value="onIdInput"
                />
            </div>

            <Select
                v-model="type"
                :options="typeOptions"
                optionLabel="label"
                optionValue="value"
                :placeholder="trans('properties.filters.type')"
                showClear
                class="w-full"
            />

            <div
                @keydown.capture="blockDisallowedAddressKey"
                @beforeinput.capture="blockDisallowedAddressBeforeInput"
            >
                <InputText
                    :modelValue="street"
                    :placeholder="trans('properties.filters.street')"
                    class="w-full"
                    @update:model-value="onStreetInput"
                />
            </div>

            <div
                @keydown.capture="blockNonDigitKey"
                @beforeinput.capture="blockNonDigitBeforeInput"
            >
                <InputText
                    :modelValue="number"
                    :placeholder="trans('properties.filters.number')"
                    class="w-full font-mono"
                    inputmode="numeric"
                    @update:model-value="onNumberInput"
                />
            </div>

            <div
                @keydown.capture="blockDisallowedAddressKey"
                @beforeinput.capture="blockDisallowedAddressBeforeInput"
            >
                <InputText
                    :modelValue="neighborhood"
                    :placeholder="trans('properties.filters.neighborhood')"
                    class="w-full"
                    @update:model-value="onNeighborhoodInput"
                />
            </div>

            <Select
                v-model="personId"
                :options="peopleOptions"
                optionLabel="label"
                optionValue="value"
                :placeholder="trans('properties.filters.owner')"
                filter
                showClear
                class="w-full"
            />

            <Select
                v-model="status"
                :options="statusOptions"
                optionLabel="label"
                optionValue="value"
                :placeholder="trans('properties.filters.status')"
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
