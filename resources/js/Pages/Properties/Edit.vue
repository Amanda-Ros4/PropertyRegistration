<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Check, FileText, Hash, Loader2 } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import FormCard from '@/Components/FormCard.vue';
import FormField from '@/Components/FormField.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import AppSelect from '@/Components/AppSelect.vue';
import { Input } from '@/Components/ui/input';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { cn } from '@/lib/utils';
import {
    CEP_INPUT_MAX_LENGTH,
    blockDisallowedAddressBeforeInput,
    blockDisallowedAddressKey,
    blockNonDigitBeforeInput,
    blockNonDigitKey,
    formatAddressInput,
    formatAreaInput,
    formatCepDisplay,
    formatCepInput,
    formatCpfDisplay,
    stripNonDigits,
} from '@/utils/formatting';
import { fetchAddressByCep } from '@/utils/viacep';
import { useAppToast } from '@/composables/useAppToast';
import { usePrecognitiveForm } from '@/composables/usePrecognitiveForm';
import PropertyDocumentsField from '@/Components/PropertyDocumentsField.vue';
import PropertyEndorsementsField from '@/Components/PropertyEndorsementsField.vue';

const { showValidationErrorToast } = useAppToast();

const props = defineProps({
    property: { type: Object, required: true },
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

const statusValue = computed(() => props.property.status?.value ?? props.property.status);
const statusLabel = computed(() =>
    statusValue.value
        ? trans(`properties.statuses.${statusValue.value}`)
        : '—',
);
const statusSeverity = computed(() =>
    statusValue.value === 'active' ? 'success' : 'secondary',
);

const cepLookupError = ref('');
const cepLoading = ref(false);
const lastFetchedCep = ref(
    props.property.cep && stripNonDigits(props.property.cep, 8).length === 8
        ? stripNonDigits(props.property.cep, 8)
        : null,
);

function formatAreaDisplay(value) {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    return formatAreaInput(String(value));
}

const landAreaDisplay = computed(() => formatAreaDisplay(props.property.land_area));
const buildingAreaDisplay = computed(() => formatAreaDisplay(props.property.building_area));

const { form, validateField } = usePrecognitiveForm('put', route('properties.update', props.property.id), {
    person_id: props.property.person_id,
    type: props.property.type?.value ?? props.property.type,
    cep: props.property.cep ? formatCepDisplay(props.property.cep) : '',
    street: formatAddressInput(props.property.street),
    number: props.property.number ? stripNonDigits(props.property.number) : '',
    neighborhood: formatAddressInput(props.property.neighborhood),
    complement: formatAddressInput(props.property.complement ?? ''),
});

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
    syncMasked('number', (v) => stripNonDigits(v).slice(0, 60), value);
}

function onStreetInput(value) {
    syncMasked('street', (v) => formatAddressInput(v).slice(0, 60), value);
}

function onNeighborhoodInput(value) {
    syncMasked('neighborhood', (v) => formatAddressInput(v).slice(0, 60), value);
}

function onComplementInput(value) {
    syncMasked('complement', (v) => formatAddressInput(v).slice(0, 60), value);
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

function openIndividualReport() {
    window.open(route('properties.report.individual', props.property.id), '_blank');
}

function submit() {
    form.put(route('properties.update', props.property.id), {
        onError: showValidationErrorToast,
    });
}
</script>

<template>
    <AppLayout :title="trans('properties.edit')">
        <Head :title="trans('properties.edit')" />

        <PageHeader
            :title="trans('properties.edit')"
            backRoute="properties.index"
            :backLabel="trans('common.back')"
        >
            <template #actions>
                <SecondaryButton
                    type="button"
                    @click="openIndividualReport"
                >
                    {{ trans('properties.reports.individual') }}
                </SecondaryButton>
            </template>
        </PageHeader>

        <FormCard>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="flex items-center gap-3 p-4 bg-indigo-50 dark:bg-indigo-950/30 rounded-lg border border-indigo-100 dark:border-indigo-900">
                    <Hash class="size-5 text-indigo-500 shrink-0" />
                    <div>
                        <p class="text-xs text-indigo-500 dark:text-indigo-400 font-medium uppercase tracking-wide">
                            {{ trans('properties.fields.municipal_registration') }}
                        </p>
                        <p class="text-xl font-bold text-indigo-700 dark:text-indigo-300">#{{ property.id }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-900/40 rounded-lg border border-slate-200 dark:border-slate-800">
                    <div class="flex-1">
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wide mb-1">
                            {{ trans('properties.fields.status') }}
                        </p>
                        <div class="flex flex-wrap items-center gap-3">
                            <StatusBadge :value="statusLabel" :severity="statusSeverity" />
                        </div>
                        <p class="text-xs text-slate-400 mt-2">
                            {{ trans('properties.hint_status_endorsements') }}
                        </p>
                    </div>
                </div>
            </div>

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
                            @change="() => { form.clearErrors('type'); validateField('type'); }"
                        />
                    </FormField>

                    <FormField
                        :label="trans('properties.fields.land_area')"
                        class="md:col-span-1"
                        :hint="trans('properties.hint_areas_endorsements')"
                    >
                        <Input
                            :model-value="landAreaDisplay"
                            class="w-full"
                            disabled
                        />
                    </FormField>

                    <FormField
                        :label="trans('properties.fields.building_area')"
                        class="md:col-span-1"
                        :hint="trans('properties.hint_areas_endorsements')"
                    >
                        <Input
                            :model-value="buildingAreaDisplay"
                            class="w-full"
                            disabled
                        />
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
                                :maxlength="60"
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
                                :maxlength="60"
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
                                :maxlength="60"
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
                                :maxlength="60"
                                @update:model-value="onComplementInput"
                                @blur="validateField('complement')"
                                @change="form.clearErrors('complement')"
                            />
                        </div>
                    </FormField>

                    <PropertyDocumentsField
                        :property-id="property.id"
                        :documents="property.documents ?? []"
                    />

                    <PropertyEndorsementsField
                        :property-id="property.id"
                        :endorsements="property.endorsements ?? []"
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
