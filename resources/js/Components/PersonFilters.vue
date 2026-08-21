<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import BirthDateInput from '@/Components/BirthDateInput.vue';
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

watch([name, cpf], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 220);
});

watch(birthDate, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        const digits = stripNonDigits(birthDate.value);
        if (digits.length === 0 || digits.length === 8) {
            applyFilters();
        }
    }, 220);
});

watch(gender, () => applyFilters());

watch(search, () => {
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
    <div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm space-y-3">
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
            {{ trans('people.filters.heading') }}
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <InputText
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
                <InputText
                    :modelValue="cpf"
                    :placeholder="trans('people.filters.cpf')"
                    class="w-full font-mono"
                    inputmode="numeric"
                    :maxlength="CPF_INPUT_MAX_LENGTH"
                    @update:model-value="onCpfInput"
                />
            </div>

            <Select
                v-model="gender"
                :options="genderOptions"
                optionLabel="label"
                optionValue="value"
                :placeholder="trans('people.filters.gender')"
                showClear
                class="w-full"
            />
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <div
                class="flex-1"
                @keydown.capture="blockDisallowedSearchKey"
                @beforeinput.capture="blockDisallowedSearchBeforeInput"
            >
                <IconField class="w-full">
                    <InputIcon class="pi pi-search" />
                    <InputText
                        :modelValue="search"
                        :placeholder="trans('people.search_placeholder')"
                        class="w-full"
                        @update:model-value="onSearchInput"
                    />
                </IconField>
            </div>

            <Button
                v-if="hasActiveFilters"
                :label="trans('common.clear')"
                icon="pi pi-filter-slash"
                outlined
                severity="secondary"
                class="w-full sm:w-auto"
                @click="clearFilters"
            />
        </div>
    </div>
</template>
