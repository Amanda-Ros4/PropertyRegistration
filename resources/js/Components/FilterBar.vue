<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { FilterX, Search } from '@lucide/vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import AppSelect from '@/Components/AppSelect.vue';
import FilterPanel from '@/Components/FilterPanel.vue';
import { useFilterSyncGuard } from '@/composables/useFilterSyncGuard';
import {
    blockDisallowedSearchBeforeInput,
    blockDisallowedSearchKey,
    formatSearchInput,
} from '@/utils/formatting';

const props = defineProps({
    routeName: { type: String, required: true },
    heading: { type: String, default: null },
    searchPlaceholder: { type: String, default: '' },
    initialSearch: { type: String, default: '' },
    selectOptions: { type: Array, default: null },
    selectOptionLabel: { type: String, default: 'label' },
    selectOptionValue: { type: String, default: 'value' },
    selectPlaceholder: { type: String, default: '' },
    initialSelectValue: { type: [String, Number], default: null },
    selectFilterKey: { type: String, default: 'person_id' },
});

const { runSyncedFromProps, shouldSkipFilterApply } = useFilterSyncGuard();

const search = ref(formatSearchInput(props.initialSearch ?? ''));
const selectedFilter = ref(props.initialSelectValue ?? null);
let debounceTimer = null;

const hasActiveFilters = computed(() =>
    Boolean(formatSearchInput(search.value).trim() || selectedFilter.value),
);

watch(
    () => [props.initialSearch, props.initialSelectValue],
    ([nextSearch, nextSelectValue]) => {
        runSyncedFromProps(() => {
            search.value = formatSearchInput(nextSearch ?? '');
            selectedFilter.value = nextSelectValue ?? null;
        });
    },
);

watch(search, () => {
    if (shouldSkipFilterApply()) {
        return;
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 180);
});

watch(selectedFilter, () => {
    if (shouldSkipFilterApply()) {
        return;
    }

    applyFilters();
});

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
</script>

<template>
    <FilterPanel :title="heading">
        <div class="flex flex-col sm:flex-row gap-3">
            <div
                class="relative flex-1"
                @keydown.capture="blockDisallowedSearchKey"
                @beforeinput.capture="blockDisallowedSearchBeforeInput"
            >
                <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                <Input
                    :model-value="search"
                    :placeholder="searchPlaceholder"
                    class="w-full pl-9"
                    @update:model-value="onSearchInput"
                />
            </div>

            <AppSelect
                v-if="selectOptions"
                v-model="selectedFilter"
                :options="selectOptions"
                :option-label="selectOptionLabel"
                :option-value="selectOptionValue"
                :placeholder="selectPlaceholder"
                show-clear
                class="w-full sm:max-w-xs"
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
