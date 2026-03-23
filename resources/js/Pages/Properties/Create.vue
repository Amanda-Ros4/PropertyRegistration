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
    formatCepInput,
    formatCpfDisplay,
    stripNonDigits,
} from '@/utils/formatting';
import { fetchAddressByCep } from '@/utils/viacep';

const props = defineProps({
    people: { type: Array, default: () => [] },
});

const peopleOptions = computed(() =>
    props.people.map(p => ({
        value: p.id,
        label: `${p.name} — ${formatCpfDisplay(p.cpf)}`,
    }))
);

const cepLookupError = ref('');
const cepLoading = ref(false);
/** Evita nova requisição ao ViaCEP para o mesmo CEP já consultado. */
const lastFetchedCep = ref(null);

const form = useForm({
    person_id: null,
    cep: '',
    street: '',
    number: '',
    neighborhood: '',
    complement: '',
});

const cepErrorDisplay = computed(() => form.errors.cep || cepLookupError.value);

function onCepInput(value) {
    form.cep = formatCepInput(value);
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
        form.street = data.logradouro;
    }
    if (data.bairro) {
        form.neighborhood = data.bairro;
    }
    if (data.complemento && String(form.complement ?? '').trim() === '') {
        form.complement = data.complemento;
    }
    form.clearErrors('cep');
    form.clearErrors('street');
    form.clearErrors('neighborhood');
}

function submit() {
    form.post(route('properties.store'));
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
                    <!-- Owner -->
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

                    <!-- CEP (ViaCEP — consulta ao completar 8 dígitos) -->
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
                                @input="onCepInput($event.target.value)"
                                @change="form.clearErrors('cep')"
                            />
                            <i
                                v-show="cepLoading"
                                class="pi pi-spin pi-spinner absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"
                                aria-hidden="true"
                            />
                        </div>
                    </FormField>

                    <!-- Street -->
                    <FormField
                        class="md:col-span-2"
                        :label="trans('properties.fields.street')"
                        :error="form.errors.street"
                        required
                    >
                        <InputText
                            v-model="form.street"
                            :placeholder="trans('properties.placeholders.street')"
                            :invalid="!!form.errors.street"
                            class="w-full"
                            @change="form.clearErrors('street')"
                        />
                    </FormField>

                    <!-- Number -->
                    <FormField
                        :label="trans('properties.fields.number')"
                        :error="form.errors.number"
                        required
                    >
                        <InputText
                            v-model="form.number"
                            :placeholder="trans('properties.placeholders.number')"
                            :invalid="!!form.errors.number"
                            class="w-full"
                            @change="form.clearErrors('number')"
                        />
                    </FormField>

                    <!-- Neighborhood -->
                    <FormField
                        :label="trans('properties.fields.neighborhood')"
                        :error="form.errors.neighborhood"
                        required
                    >
                        <InputText
                            v-model="form.neighborhood"
                            :placeholder="trans('properties.placeholders.neighborhood')"
                            :invalid="!!form.errors.neighborhood"
                            class="w-full"
                            @change="form.clearErrors('neighborhood')"
                        />
                    </FormField>

                    <!-- Complement -->
                    <FormField
                        class="md:col-span-2"
                        :label="trans('properties.fields.complement')"
                        :error="form.errors.complement"
                    >
                        <InputText
                            v-model="form.complement"
                            :placeholder="trans('properties.placeholders.complement')"
                            :invalid="!!form.errors.complement"
                            class="w-full"
                            @change="form.clearErrors('complement')"
                        />
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
