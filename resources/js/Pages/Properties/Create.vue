<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Check, Loader2 } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import FormCard from '@/Components/FormCard.vue';
import FormField from '@/Components/FormField.vue';
import AppSelect from '@/Components/AppSelect.vue';
import { Input } from '@/Components/ui/input';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { cn } from '@/lib/utils';
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
import { usePrecognitiveForm } from '@/composables/usePrecognitiveForm';
import { usePropertyTypeAreas } from '@/composables/usePropertyTypeAreas';
import PropertyDocumentsField from '@/Components/PropertyDocumentsField.vue';

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

const { form, validateField } = usePrecognitiveForm('post', route('properties.store'), {
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

const pendingDocuments = ref([]);

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
            documents: pendingDocuments.value.map((item) => item.file),
        }))
        .post(route('properties.store'), {
            forceFormData: true,
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
                        <AppSelect
                            v-model="form.person_id"
                            :options="peopleOptions"
                            :placeholder="trans('properties.placeholders.owner')"
                            :invalid="!!form.errors.person_id"
                            filter
                            class="w-full"
                            @change="() => { form.clearErrors('person_id'); validateField('person_id'); }"
                        />
                    </FormField>

                    <FormField
                        :label="trans('properties.fields.type')"
                        :error="form.errors.type"
                        required
                    >
                        <AppSelect
                            v-model="form.type"
                            :options="typeOptions"
                            :placeholder="trans('properties.placeholders.type')"
                            :invalid="!!form.errors.type"
                            class="w-full"
                            @change="() => { onTypeChange(); validateField('type'); validateField('land_area'); validateField('building_area'); }"
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
                            <Input
                                :model-value="form.land_area"
                                inputmode="decimal"
                                :placeholder="trans('properties.placeholders.land_area')"
                                :class="cn('w-full', form.errors.land_area && 'border-destructive')"
                                :disabled="landAreaLocked"
                                @update:model-value="onLandAreaInput"
                                @blur="validateField('land_area')"
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
                            <Input
                                :model-value="form.building_area"
                                inputmode="decimal"
                                :placeholder="trans('properties.placeholders.building_area')"
                                :class="cn('w-full', form.errors.building_area && 'border-destructive')"
                                :disabled="buildingAreaLocked"
                                @update:model-value="onBuildingAreaInput"
                                @blur="validateField('building_area')"
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
                            <Input
                                :model-value="form.cep"
                                type="text"
                                inputmode="numeric"
                                autocomplete="postal-code"
                                :placeholder="trans('properties.placeholders.cep')"
                                :class="cn('w-full pr-10', (form.errors.cep || cepLookupError) && 'border-destructive')"
                                :maxlength="CEP_INPUT_MAX_LENGTH"
                                :disabled="cepLoading"
                                @update:model-value="onCepInput"
                                @blur="validateField('cep')"
                                @change="form.clearErrors('cep')"
                            />
                            <Loader2
                                v-show="cepLoading"
                                class="absolute right-3 top-1/2 size-4 -translate-y-1/2 animate-spin text-slate-400 pointer-events-none"
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
                            <Input
                                :model-value="form.street"
                                :placeholder="trans('properties.placeholders.street')"
                                :class="cn('w-full', form.errors.street && 'border-destructive')"
                                @update:model-value="onStreetInput"
                                @blur="validateField('street')"
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
                            <Input
                                :model-value="form.number"
                                inputmode="numeric"
                                :placeholder="trans('properties.placeholders.number')"
                                :class="cn('w-full', form.errors.number && 'border-destructive')"
                                @update:model-value="onNumberInput"
                                @blur="validateField('number')"
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
                            <Input
                                :model-value="form.neighborhood"
                                :placeholder="trans('properties.placeholders.neighborhood')"
                                :class="cn('w-full', form.errors.neighborhood && 'border-destructive')"
                                @update:model-value="onNeighborhoodInput"
                                @blur="validateField('neighborhood')"
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
                            <Input
                                :model-value="form.complement"
                                :placeholder="trans('properties.placeholders.complement')"
                                :class="cn('w-full', form.errors.complement && 'border-destructive')"
                                @update:model-value="onComplementInput"
                                @blur="validateField('complement')"
                                @change="form.clearErrors('complement')"
                            />
                        </div>
                    </FormField>

                    <PropertyDocumentsField
                        v-model="pendingDocuments"
                        :errors="form.errors"
                    />
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <SecondaryButton
                        type="button"
                        @click="router.visit(route('properties.index'))"
                    >
                        {{ trans('common.cancel') }}
                    </SecondaryButton>
                    <PrimaryButton
                        type="submit"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ trans('common.save') }}
                    </PrimaryButton>
                </div>
            </form>
        </FormCard>
    </AppLayout>
</template>
