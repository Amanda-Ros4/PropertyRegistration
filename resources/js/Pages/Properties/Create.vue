<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import FormCard from '@/Components/FormCard.vue';
import FormField from '@/Components/FormField.vue';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Button from 'primevue/button';
import {
    CEP_INPUT_MAX_LENGTH,
    blockDisallowedAddressBeforeInput,
    blockDisallowedAddressKey,
    blockNonAreaBeforeInput,
    blockNonAreaKey,
    blockNonDigitBeforeInput,
    blockNonDigitKey,
    formatAddressInput,
    formatAreaInput,
    formatCepInput,
    formatCpfDisplay,
    stripNonDigits,
} from '@/utils/formatting';
import { fetchAddressByCep } from '@/utils/viacep';
import { useAppToast } from '@/composables/useAppToast';
import { usePropertyTypeAreas } from '@/composables/usePropertyTypeAreas';

const { showValidationErrorToast } = useAppToast();

const props = defineProps({
    people: { type: Array, default: () => [] },
});

const peopleOptions = computed(() =>
    props.people.map(p => ({
        value: p.id,
        label: `${p.name} — ${formatCpfDisplay(p.cpf)}`,
    })),
);

const typeOptions = computed(() => [
    { value: 'land', label: trans('properties.types.land') },
    { value: 'house', label: trans('properties.types.house') },
    { value: 'apartment', label: trans('properties.types.apartment') },
]);

const cepLookupError = ref('');
const cepLoading = ref(false);
const lastFetchedCep = ref(null);

const form = useForm({
    person_id: null,
    type: null,
    land_area: '',
    building_area: '',
    cep: '',
    street: '',
    number: '',
    neighborhood: '',
    complement: '',
});

const {
    landAreaRequired,
    buildingAreaRequired,
    landAreaLocked,
    buildingAreaLocked,
    onTypeChange,
    onLandAreaInput: applyLandAreaInput,
    onBuildingAreaInput: applyBuildingAreaInput,
    areasForSubmit,
} = usePropertyTypeAreas(form);

const cepErrorDisplay = computed(() => form.errors.cep || cepLookupError.value);

function syncMasked(field, formatter, value) {
    const formatted = formatter(value);
    if (formatted === form[field]) {
        form[field] = `${formatted}\u200b`;
        queueMicrotask(() => {
            form[field] = formatted;
        });
        return;
    }
    form[field] = formatted;
}

function onCepInput(value) {
    syncMasked('cep', formatCepInput, value);
    cepLookupError.value = '';

    const digits = stripNonDigits(form.cep, 8);
    if (digits.length !== 8) {
        lastFetchedCep.value = null;
        return;
    }
    if (lastFetchedCep.value === digits) {
        return;
    }

    lookupCepDigits(digits);
}

function onNumberInput(value) {
    syncMasked('number', (v) => stripNonDigits(v), value);
}

function onLandAreaInput(value) {
    applyLandAreaInput(syncMasked, formatAreaInput, value);
}

function onBuildingAreaInput(value) {
    applyBuildingAreaInput(syncMasked, formatAreaInput, value);
}

function onStreetInput(value) {
    syncMasked('street', formatAddressInput, value);
}

function onNeighborhoodInput(value) {
    syncMasked('neighborhood', formatAddressInput, value);
}

function onComplementInput(value) {
    syncMasked('complement', formatAddressInput, value);
}

async function lookupCepDigits(digits) {
    cepLookupError.value = '';
    cepLoading.value = true;
    const result = await fetchAddressByCep(digits);
    cepLoading.value = false;

    if (!result.ok) {
        lastFetchedCep.value = null;
        if (result.reason === 'not_found') {
            cepLookupError.value = trans('properties.errors.cep_not_found');
        } else if (result.reason === 'network') {
            cepLookupError.value = trans('properties.errors.cep_network');
        }
        return;
    }

    lastFetchedCep.value = digits;

    const { data } = result;
    if (data.logradouro) {
        form.street = formatAddressInput(data.logradouro);
    }
    if (data.bairro) {
        form.neighborhood = formatAddressInput(data.bairro);
    }
    if (data.complemento && String(form.complement ?? '').trim() === '') {
        form.complement = formatAddressInput(data.complemento);
    }
    form.clearErrors('cep');
    form.clearErrors('street');
    form.clearErrors('neighborhood');
}

function submit() {
    form
        .transform((data) => ({
            ...data,
            ...areasForSubmit(data),
        }))
        .post(route('properties.store'), {
            onError: showValidationErrorToast,
        });
}
</script>

<template>
    <AppLayout :title="trans('properties.create')">
        <Head :title="trans('properties.create')" />

        <PageHeader
            :title="trans('properties.create')"
            backRoute="properties.index"
            :backLabel="trans('common.back')"
        />

        <FormCard>
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <FormField
                        class="md:col-span-2"
                        :label="trans('properties.fields.owner')"
                        :error="form.errors.person_id"
                        required
                    >
                        <Select
                            v-model="form.person_id"
                            :options="peopleOptions"
                            optionLabel="label"
                            optionValue="value"
                            :placeholder="trans('properties.placeholders.owner')"
                            :invalid="!!form.errors.person_id"
                            filter
                            class="w-full"
                            @change="form.clearErrors('person_id')"
                        />
                    </FormField>

                    <FormField
                        :label="trans('properties.fields.type')"
                        :error="form.errors.type"
                        required
                    >
                        <Select
                            v-model="form.type"
                            :options="typeOptions"
                            optionLabel="label"
                            optionValue="value"
                            :placeholder="trans('properties.placeholders.type')"
                            :invalid="!!form.errors.type"
                            class="w-full"
                            @change="onTypeChange"
                        />
                    </FormField>

                    <FormField
                        :label="trans('properties.fields.land_area')"
                        :error="form.errors.land_area"
                        :required="landAreaRequired"
                        :hint="landAreaLocked ? trans('properties.hint_land_area_apartment') : null"
                    >
                        <div
                            @keydown.capture="blockNonAreaKey"
                            @beforeinput.capture="blockNonAreaBeforeInput"
                        >
                            <InputText
                                :modelValue="form.land_area"
                                inputmode="decimal"
                                :placeholder="trans('properties.placeholders.land_area')"
                                :invalid="!!form.errors.land_area"
                                class="w-full font-mono"
                                :disabled="landAreaLocked"
                                @update:model-value="onLandAreaInput"
                                @change="form.clearErrors('land_area')"
                            />
                        </div>
                    </FormField>

                    <FormField
                        :label="trans('properties.fields.building_area')"
                        :error="form.errors.building_area"
                        :required="buildingAreaRequired"
                        :hint="buildingAreaLocked ? trans('properties.hint_building_area_land') : null"
                    >
                        <div
                            @keydown.capture="blockNonAreaKey"
                            @beforeinput.capture="blockNonAreaBeforeInput"
                        >
                            <InputText
                                :modelValue="form.building_area"
                                inputmode="decimal"
                                :placeholder="trans('properties.placeholders.building_area')"
                                :invalid="!!form.errors.building_area"
                                class="w-full font-mono"
                                :disabled="buildingAreaLocked"
                                @update:model-value="onBuildingAreaInput"
                                @change="form.clearErrors('building_area')"
                            />
                        </div>
                    </FormField>

                    <FormField
                        class="md:col-span-2"
                        :label="trans('properties.fields.cep')"
                        :error="cepErrorDisplay"
                        :hint="trans('properties.hint_cep')"
                    >
                        <div class="relative">
                            <InputText
                                :modelValue="form.cep"
                                type="text"
                                inputmode="numeric"
                                autocomplete="postal-code"
                                :placeholder="trans('properties.placeholders.cep')"
                                :invalid="!!(form.errors.cep || cepLookupError)"
                                class="w-full font-mono pr-10"
                                :maxlength="CEP_INPUT_MAX_LENGTH"
                                :disabled="cepLoading"
                                @update:model-value="onCepInput"
                                @change="form.clearErrors('cep')"
                            />
                            <i
                                v-show="cepLoading"
                                class="pi pi-spin pi-spinner absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"
                                aria-hidden="true"
                            />
                        </div>
                    </FormField>

                    <FormField
                        class="md:col-span-2"
                        :label="trans('properties.fields.street')"
                        :error="form.errors.street"
                        required
                    >
                        <div
                            @keydown.capture="blockDisallowedAddressKey"
                            @beforeinput.capture="blockDisallowedAddressBeforeInput"
                        >
                            <InputText
                                :modelValue="form.street"
                                :placeholder="trans('properties.placeholders.street')"
                                :invalid="!!form.errors.street"
                                class="w-full"
                                @update:model-value="onStreetInput"
                                @change="form.clearErrors('street')"
                            />
                        </div>
                    </FormField>

                    <FormField
                        :label="trans('properties.fields.number')"
                        :error="form.errors.number"
                        required
                    >
                        <div
                            @keydown.capture="blockNonDigitKey"
                            @beforeinput.capture="blockNonDigitBeforeInput"
                        >
                            <InputText
                                :modelValue="form.number"
                                inputmode="numeric"
                                :placeholder="trans('properties.placeholders.number')"
                                :invalid="!!form.errors.number"
                                class="w-full font-mono"
                                @update:model-value="onNumberInput"
                                @change="form.clearErrors('number')"
                            />
                        </div>
                    </FormField>

                    <FormField
                        :label="trans('properties.fields.neighborhood')"
                        :error="form.errors.neighborhood"
                        required
                    >
                        <div
                            @keydown.capture="blockDisallowedAddressKey"
                            @beforeinput.capture="blockDisallowedAddressBeforeInput"
                        >
                            <InputText
                                :modelValue="form.neighborhood"
                                :placeholder="trans('properties.placeholders.neighborhood')"
                                :invalid="!!form.errors.neighborhood"
                                class="w-full"
                                @update:model-value="onNeighborhoodInput"
                                @change="form.clearErrors('neighborhood')"
                            />
                        </div>
                    </FormField>

                    <FormField
                        class="md:col-span-2"
                        :label="trans('properties.fields.complement')"
                        :error="form.errors.complement"
                    >
                        <div
                            @keydown.capture="blockDisallowedAddressKey"
                            @beforeinput.capture="blockDisallowedAddressBeforeInput"
                        >
                            <InputText
                                :modelValue="form.complement"
                                :placeholder="trans('properties.placeholders.complement')"
                                :invalid="!!form.errors.complement"
                                class="w-full"
                                @update:model-value="onComplementInput"
                                @change="form.clearErrors('complement')"
                            />
                        </div>
                    </FormField>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <Button
                        type="button"
                        :label="trans('common.cancel')"
                        severity="secondary"
                        outlined
                        @click="router.visit(route('properties.index'))"
                    />
                    <Button
                        type="submit"
                        :label="trans('common.save')"
                        icon="pi pi-check"
                        :loading="form.processing"
                    />
                </div>
            </form>
        </FormCard>
    </AppLayout>
</template>
