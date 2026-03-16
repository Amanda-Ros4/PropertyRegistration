<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';

const props = defineProps({
    routeName: { type: String, required: true },
    searchPlaceholder: { type: String, default: '' },
    initialSearch: { type: String, default: '' },
    selectOptions: { type: Array, default: null },  // [{ value, label }]
    selectOptionLabel: { type: String, default: 'label' },
    selectOptionValue: { type: String, default: 'value' },
    selectPlaceholder: { type: String, default: '' },
    initialSelectValue: { type: [String, Number], default: null },
    selectFilterKey: { type: String, default: 'person_id' },
});

const search = ref(props.initialSearch ?? '');
const selectedFilter = ref(props.initialSelectValue ?? null);
let debounceTimer = null;

watch(search, (value) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 400);
});

watch(selectedFilter, () => applyFilters());

function applyFilters() {
    const params = {};
    if (search.value) params.search = search.value;
    if (selectedFilter.value) params[props.selectFilterKey] = selectedFilter.value;

    router.get(route(props.routeName), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function clearFilters() {
    search.value = '';
    selectedFilter.value = null;
    router.get(route(props.routeName), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

const hasActiveFilters = () => search.value || selectedFilter.value;
</script>

<template>
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <IconField class="flex-1">
            <InputIcon class="pi pi-search" />
            <InputText
                v-model="search"
                :placeholder="searchPlaceholder"
                class="w-full"
            />
        </IconField>

        <Select
            v-if="selectOptions"
            v-model="selectedFilter"
            :options="selectOptions"
            :optionLabel="selectOptionLabel"
            :optionValue="selectOptionValue"
            :placeholder="selectPlaceholder"
            showClear
            class="w-full sm:w-64"
        />

        <Button
            v-if="hasActiveFilters()"
            :label="trans('common.clear')"
            icon="pi pi-filter-slash"
            outlined
            severity="secondary"
            @click="clearFilters"
        />
    </div>
</template>
