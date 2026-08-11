<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import {
    blockDisallowedSearchBeforeInput,
    blockDisallowedSearchKey,
    formatSearchInput,
} from '@/utils/formatting';

const props = defineProps({
    routeName: { type: String, required: true },
    searchPlaceholder: { type: String, default: '' },
    initialSearch: { type: String, default: '' },
    selectOptions: { type: Array, default: null },
    selectOptionLabel: { type: String, default: 'label' },
    selectOptionValue: { type: String, default: 'value' },
    selectPlaceholder: { type: String, default: '' },
    initialSelectValue: { type: [String, Number], default: null },
    selectFilterKey: { type: String, default: 'person_id' },
});

const search = ref(formatSearchInput(props.initialSearch ?? ''));
const selectedFilter = ref(props.initialSelectValue ?? null);
let debounceTimer = null;

watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 180);
});

watch(selectedFilter, () => applyFilters());

function onSearchInput(value) {
    const sanitized = formatSearchInput(value);
    if (sanitized === search.value) {
        search.value = `${sanitized}\u200b`;
        queueMicrotask(() => {
            search.value = sanitized;
        });
        return;
    }
    search.value = sanitized;
}

function applyFilters() {
    const params = {};
    const term = formatSearchInput(search.value).trim();
    if (term) params.search = term;
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
        <div
            class="flex-1"
            @keydown.capture="blockDisallowedSearchKey"
            @beforeinput.capture="blockDisallowedSearchBeforeInput"
        >
            <IconField class="w-full">
                <InputIcon class="pi pi-search" />
                <InputText
                    :modelValue="search"
                    :placeholder="searchPlaceholder"
                    class="w-full"
                    @update:model-value="onSearchInput"
                />
            </IconField>
        </div>

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
