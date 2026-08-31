<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Input } from '@/Components/ui/input';
import AppSelect from '@/Components/AppSelect.vue';
import FilterPanel from '@/Components/FilterPanel.vue';
import {
    stripNonDigits,
} from '@/utils/formatting';

const props = defineProps({
    filters: { type: Object, default: () => ({}) },
    peopleOptions: { type: Array, default: () => [] },
});

// Estado estritamente local
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

// Bloqueia a inserção de numéricos e caracteres especiais antes de entrarem no DOM
function handleNumericBeforeInput(e) {
    if (e.data && !/^\d+$/.test(e.data)) {
        e.preventDefault();
    }
}

// Bloqueia caracteres especiais não permitidos em endereços
function handleAddressBeforeInput(e) {
    // Permite letras (com acentos), números, espaço, hífen, vírgula e ponto
    const allowedRegex = /^[a-zA-Z0-9áàâãéèêíïóôõöúçñÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ\s\-\,\.]*$/;
    if (e.data && !allowedRegex.test(e.data)) {
        e.preventDefault();
    }
}

// Executa a busca com debounce ao alterar os campos de texto
watch([id, street, number, neighborhood], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 300);
});

// Executa a busca imediatamente para selects
watch([type, personId, status], () => {
    applyFilters();
});

function applyFilters() {
    const params = {};

    const idValue = stripNonDigits(id.value);
    if (idValue) params.id = idValue;

    if (type.value) params.type = type.value;

    const streetValue = street.value.trim();
    if (streetValue) params.street = streetValue;

    const numberValue = stripNonDigits(number.value);
    if (numberValue) params.number = numberValue;

    const neighborhoodValue = neighborhood.value.trim();
    if (neighborhoodValue) params.neighborhood = neighborhoodValue;

    if (personId.value) params.person_id = personId.value;
    if (status.value) params.status = status.value;

    router.get(route('properties.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['properties'],
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
            <div>
                <Input v-model="id" :placeholder="trans('properties.filters.municipal_registration')" class="w-full"
                    inputmode="numeric" maxlength="15" @beforeinput="handleNumericBeforeInput" />
            </div>

            <AppSelect v-model="type" :options="typeOptions" :placeholder="trans('properties.filters.type')" show-clear
                class="w-full" />

            <div>
                <Input v-model="street" :placeholder="trans('properties.filters.street')" class="w-full"
                    @beforeinput="handleAddressBeforeInput" maxlength="60" />
            </div>

            <div>
                <Input v-model="number" :placeholder="trans('properties.filters.number')" class="w-full"
                    inputmode="numeric" @beforeinput="handleNumericBeforeInput" maxlength="60"/>
            </div>

            <div>
                <Input v-model="neighborhood" :placeholder="trans('properties.filters.neighborhood')" class="w-full"
                    @beforeinput="handleAddressBeforeInput" maxlength="60" />
            </div>

            <AppSelect v-model="personId" :options="peopleOptions" :placeholder="trans('properties.filters.owner')"
                filter show-clear class="w-full" />

            <AppSelect v-model="status" :options="statusOptions" :placeholder="trans('properties.filters.status')"
                show-clear class="w-full" />
        </div>

        <template v-if="hasActiveFilters" #actions>
            <SecondaryButton type="button" class="w-full sm:w-auto" @click="clearFilters">
                {{ trans('common.clear') }}
            </SecondaryButton>
        </template>
    </FilterPanel>
</template>