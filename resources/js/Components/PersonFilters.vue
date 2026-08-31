<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Search } from '@lucide/vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Input } from '@/Components/ui/input';
import AppSelect from '@/Components/AppSelect.vue';
import BirthDateInput from '@/Components/BirthDateInput.vue';
import FilterPanel from '@/Components/FilterPanel.vue';
import {
    formatBirthDateForSubmit,
    formatCpfInput,
    stripNonDigits,
    toBirthDateInputValue,
    CPF_INPUT_MAX_LENGTH,
} from '@/utils/formatting';

const props = defineProps({
    filters: { type: Object, default: () => ({}) },
});

// Estado estritamente local para impedir sobreposição enquanto digita
const name = ref(props.filters.name ?? '');
const birthDate = ref(toBirthDateInputValue(props.filters.birth_date ?? ''));
const cpf = ref(props.filters.cpf ? formatCpfInput(props.filters.cpf) : '');
const gender = ref(props.filters.gender ?? null);
const search = ref(props.filters.search ?? '');

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
        || search.value.trim(),
    ),
);

// Bloqueia caracteres especiais no Nome (permite apenas letras, acentos, espaços, hífens e apóstrofos)
function handleNameBeforeInput(e) {
    const allowedRegex = /^[a-zA-ZáàâãéèêíïóôõöúçñÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ\s\-\']*$/;
    if (e.data && !allowedRegex.test(e.data)) {
        e.preventDefault();
    }
}

// Bloqueia a inserção de não dígitos antes de desenhar no DOM (no CPF)
function handleNumericBeforeInput(e) {
    if (e.data && !/^\d+$/.test(e.data)) {
        e.preventDefault();
    }
}

// Intercepta caracteres especiais no campo de busca antes do DOM
function handleSearchBeforeInput(e) {
    const allowedRegex = /^[a-zA-Z0-9áàâãéèêíïóôõöúçñÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ\s\-\,\.\@\_\-]*$/;
    if (e.data && !allowedRegex.test(e.data)) {
        e.preventDefault();
    }
}

function onCpfInput(val) {
    cpf.value = formatCpfInput(val);
}

watch([name, cpf, search], () => {
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

watch(gender, () => {
    applyFilters();
});

function applyFilters() {
    const params = {};

    const nameValue = name.value.trim();
    if (nameValue) params.name = nameValue;

    const birthDateValue = formatBirthDateForSubmit(birthDate.value);
    if (birthDateValue) params.birth_date = birthDateValue;

    const cpfDigits = stripNonDigits(cpf.value);
    if (cpfDigits) params.cpf = cpfDigits;

    if (gender.value) params.gender = gender.value;

    const searchValue = search.value.trim();
    if (searchValue) params.search = searchValue;

    router.get(route('people.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['people'],
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
            <div>
                <Input
                    v-model="name"
                    :placeholder="trans('people.filters.name')"
                    class="w-full"
                    :maxlength="100"
                    @beforeinput="handleNameBeforeInput"
                    @input="name = name.slice(0, 100)"
                />
            </div>

            <BirthDateInput
                v-model="birthDate"
                :placeholder="trans('people.placeholders.birth_date')"
            />

            <div>
                <Input
                    :model-value="cpf"
                    :placeholder="trans('people.filters.cpf')"
                    class="w-full"
                    inputmode="numeric"
                    :maxlength="CPF_INPUT_MAX_LENGTH"
                    @beforeinput="handleNumericBeforeInput"
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

        <div class="relative w-full">
            <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground pointer-events-none" />
            <Input
                v-model="search"
                :placeholder="trans('people.search_placeholder')"
                class="w-full pl-9"
                maxlength="100"
                @beforeinput="handleSearchBeforeInput"
                @input="search = search.slice(0, 100)"
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