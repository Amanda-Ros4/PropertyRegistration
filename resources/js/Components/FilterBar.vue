<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Search } from '@lucide/vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Input } from '@/Components/ui/input';
import AppSelect from '@/Components/AppSelect.vue';
import FilterPanel from '@/Components/FilterPanel.vue';

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

// Estado estritamente local para o formulário do filtro
const search = ref(props.initialSearch ?? '');
const selectedFilter = ref(props.initialSelectValue ?? null);
let debounceTimer = null;

const hasActiveFilters = computed(() =>
    Boolean(search.value.trim() || selectedFilter.value),
);

// Intercepta e previne caracteres especiais inválidos antes de ir para o DOM
function handleSearchBeforeInput(e) {
    // Permite letras (com acentos), números, espaços, hífens, vírgulas, pontos, @ e _ (emails, CPF e nomes)
    const allowedRegex = /^[a-zA-Z0-9áàâãéèêíïóôõöúçñÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ\s\-\,\.\@\_]*$/;
    if (e.data && !allowedRegex.test(e.data)) {
        e.preventDefault();
    }
}

watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 220);
});

watch(selectedFilter, () => {
    applyFilters();
});

function applyFilters() {
    const params = {};
    const term = search.value.trim();
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
            <div class="relative flex-1">
                <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                <Input
                    v-model="search"
                    :placeholder="searchPlaceholder"
                    class="w-full pl-9"
                    @beforeinput="handleSearchBeforeInput"
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
            <SecondaryButton
                type="button"
                class="w-full sm:w-auto"
                @click="clearFilters"
            >
                {{ trans('common.clear') }}
            </SecondaryButton>
        </template>
    </FilterPanel>
</template>