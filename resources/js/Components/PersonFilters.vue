<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { FilterX, Search } from '@lucide/vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import AppSelect from '@/Components/AppSelect.vue';
import BirthDateInput from '@/Components/BirthDateInput.vue';
import FilterPanel from '@/Components/FilterPanel.vue';
import { useFilterSyncGuard } from '@/composables/useFilterSyncGuard';
import {
    blockDisallowedSearchBeforeInput,
    blockDisallowedSearchKey,
    blockNonDigitBeforeInput,
    blockNonDigitKey,
    formatBirthDateForSubmit,
    formatCpfInput,
    formatSearchInput,
    stripNonDigits,
    toBirthDateInputValue,
    CPF_INPUT_MAX_LENGTH,
} from '@/utils/formatting';

const props = defineProps({
    filters: { type: Object, default: () => ({}) },
});

const { runSyncedFromProps, shouldSkipFilterApply } = useFilterSyncGuard();

const name = ref(props.filters.name ?? '');
const birthDate = ref(toBirthDateInputValue(props.filters.birth_date ?? ''));
const cpf = ref(props.filters.cpf ? formatCpfInput(props.filters.cpf) : '');
const gender = ref(props.filters.gender ?? null);
const search = ref(formatSearchInput(props.filters.search ?? ''));

let debounceTimer = null;

const genderOptions = computed(() => [
    { value: 'male', label: trans('genders.male') },
    { value: 'female', label: trans('genders.female') },
    { value: 'other', label: trans('genders.other') },
    { value: 'prefer_not_to_say', label: trans('genders.prefer_not_to_say') },
]);

const hasActiveFilters = computed(() =>
    Boolean(
        name.value.trim()
        || formatBirthDateForSubmit(birthDate.value)
        || stripNonDigits(cpf.value)
        || gender.value
        || formatSearchInput(search.value).trim(),
    ),
);

watch(
    () => props.filters,
    (filters) => {
        runSyncedFromProps(() => {
            name.value = filters.name ?? '';
            birthDate.value = toBirthDateInputValue(filters.birth_date ?? '');
            cpf.value = filters.cpf ? formatCpfInput(filters.cpf) : '';
            gender.value = filters.gender ?? null;
            search.value = formatSearchInput(filters.search ?? '');
        });
    },
    { deep: true },
);

watch([name, cpf], () => {
    if (shouldSkipFilterApply()) {
        return;
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 220);
});

watch(birthDate, () => {
    if (shouldSkipFilterApply()) {
        return;
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        const digits = stripNonDigits(birthDate.value);
        if (digits.length === 0 || digits.length === 8) {
            applyFilters();
        }
    }, 220);
});

watch(gender, () => {
    if (shouldSkipFilterApply()) {
        return;
    }

    applyFilters();
});

watch(search, () => {
    if (shouldSkipFilterApply()) {
        return;
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 180);
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

function onCpfInput(value) {
    syncMasked(cpf, formatCpfInput, value);
}

function onSearchInput(value) {
    syncMasked(search, formatSearchInput, value);
}

function applyFilters() {
    const params = {};

    const nameValue = name.value.trim();
    if (nameValue) params.name = nameValue;

    const birthDateValue = formatBirthDateForSubmit(birthDate.value);
    if (birthDateValue) params.birth_date = birthDateValue;

    const cpfDigits = stripNonDigits(cpf.value);
    if (cpfDigits) params.cpf = cpfDigits;

    if (gender.value) params.gender = gender.value;

    const searchValue = formatSearchInput(search.value).trim();
    if (searchValue) params.search = searchValue;

    router.get(route('people.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function clearFilters() {
    name.value = '';
    birthDate.value = '';
    cpf.value = '';
    gender.value = null;
    search.value = '';

    router.get(route('people.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}
</script>

<template>
    <FilterPanel :title="trans('people.filters.heading')">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <Input
                v-model="name"
                :placeholder="trans('people.filters.name')"
                class="w-full"
            />

            <BirthDateInput
                v-model="birthDate"
                :placeholder="trans('people.placeholders.birth_date')"
            />

            <div
                @keydown.capture="blockNonDigitKey"
                @beforeinput.capture="blockNonDigitBeforeInput"
            >
                <Input
                    :model-value="cpf"
                    :placeholder="trans('people.filters.cpf')"
                    class="w-full"
                    inputmode="numeric"
                    :maxlength="CPF_INPUT_MAX_LENGTH"
                    @update:model-value="onCpfInput"
                />
            </div>

            <AppSelect
                v-model="gender"
                :options="genderOptions"
                :placeholder="trans('people.filters.gender')"
                show-clear
                class="w-full"
            />
        </div>

        <div
            class="relative w-full"
            @keydown.capture="blockDisallowedSearchKey"
            @beforeinput.capture="blockDisallowedSearchBeforeInput"
        >
            <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground pointer-events-none" />
            <Input
                :model-value="search"
                :placeholder="trans('people.search_placeholder')"
                class="w-full pl-9"
                @update:model-value="onSearchInput"
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
