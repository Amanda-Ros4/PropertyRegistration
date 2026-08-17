<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import FormField from '@/Components/FormField.vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import EmptyState from '@/Components/EmptyState.vue';
import {
    blockNonAreaBeforeInput,
    blockNonAreaKey,
    formatAreaInput,
} from '@/utils/formatting';
import { useAppToast } from '@/composables/useAppToast';

const props = defineProps({
    propertyId: { type: [Number, String], required: true },
    endorsements: { type: Array, default: () => [] },
});

const { showValidationErrorToast } = useAppToast();

const eventOptions = computed(() => [
    { value: 'I', label: trans('properties.endorsements.events.increase_in_built_area') },
    { value: 'D', label: trans('properties.endorsements.events.decrease_in_built_area') },
    { value: 'O', label: trans('properties.endorsements.events.observation') },
    { value: 'C', label: trans('properties.endorsements.events.cancellation') },
    { value: 'R', label: trans('properties.endorsements.events.reactivation') },
]);

const eventLabelKey = {
    I: 'properties.endorsements.events.increase_in_built_area',
    D: 'properties.endorsements.events.decrease_in_built_area',
    O: 'properties.endorsements.events.observation',
    C: 'properties.endorsements.events.cancellation',
    R: 'properties.endorsements.events.reactivation',
};

const form = useForm({
    event: 'O',
    measure: '',
    description: '',
});

const requiresMeasure = computed(() => form.event === 'I' || form.event === 'D');

function onMeasureInput(value) {
    form.measure = formatAreaInput(value);
}

function formatDate(value) {
    if (!value) {
        return '—';
    }

    const [year, month, day] = String(value).slice(0, 10).split('-');

    if (!year || !month || !day) {
        return value;
    }

    return `${day}/${month}/${year}`;
}

function formatMeasure(value) {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    return `${formatAreaInput(String(value))} m²`;
}

function submit() {
    form.post(route('properties.endorsements.store', props.propertyId), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.event = 'O';
        },
        onError: showValidationErrorToast,
    });
}
</script>

<template>
    <div class="md:col-span-2 space-y-6 pt-2 border-t border-gray-100 dark:border-gray-800">
        <div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                {{ trans('properties.endorsements.title') }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ trans('properties.endorsements.hint') }}
            </p>
        </div>

        <form class="grid grid-cols-1 md:grid-cols-2 gap-6" @submit.prevent="submit">
            <FormField
                class="md:col-span-2"
                :label="trans('properties.endorsements.fields.event')"
                :error="form.errors.event"
                required
            >
                <Select
                    v-model="form.event"
                    :options="eventOptions"
                    optionLabel="label"
                    optionValue="value"
                    :placeholder="trans('properties.endorsements.placeholders.event')"
                    :invalid="!!form.errors.event"
                    class="w-full"
                    @change="form.clearErrors('event')"
                />
            </FormField>

            <FormField
                v-if="requiresMeasure"
                :label="trans('properties.endorsements.fields.measure')"
                :error="form.errors.measure"
                required
            >
                <div
                    @keydown.capture="blockNonAreaKey"
                    @beforeinput.capture="blockNonAreaBeforeInput"
                >
                    <InputText
                        :modelValue="form.measure"
                        inputmode="decimal"
                        :placeholder="trans('properties.endorsements.placeholders.measure')"
                        :invalid="!!form.errors.measure"
                        class="w-full font-mono"
                        @update:model-value="onMeasureInput"
                        @change="form.clearErrors('measure')"
                    />
                </div>
            </FormField>

            <FormField
                class="md:col-span-2"
                :label="trans('properties.endorsements.fields.description')"
                :error="form.errors.description"
                required
            >
                <Textarea
                    v-model="form.description"
                    rows="4"
                    autoResize
                    :placeholder="trans('properties.endorsements.placeholders.description')"
                    :invalid="!!form.errors.description"
                    class="w-full"
                    @change="form.clearErrors('description')"
                />
            </FormField>

            <div class="md:col-span-2 flex justify-end">
                <Button
                    type="submit"
                    :label="trans('properties.endorsements.save')"
                    icon="pi pi-plus"
                    :loading="form.processing"
                />
            </div>
        </form>

        <EmptyState
            v-if="endorsements.length === 0"
            icon="pi pi-file-edit"
            :title="trans('properties.endorsements.empty')"
            :description="trans('properties.endorsements.empty_description')"
        />

        <DataTable
            v-else
            :value="endorsements"
            :rowHover="true"
            class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800"
            tableClass="w-full"
            stripedRows
        >
            <Column :header="trans('properties.endorsements.fields.date')" style="width: 120px">
                <template #body="{ data }">
                    <span class="font-mono text-sm">{{ formatDate(data.occurred_on) }}</span>
                </template>
            </Column>
            <Column :header="trans('properties.endorsements.fields.event')">
                <template #body="{ data }">
                    {{ trans(eventLabelKey[data.event?.value ?? data.event] || eventLabelKey.O) }}
                </template>
            </Column>
            <Column :header="trans('properties.endorsements.fields.measure')" style="width: 140px">
                <template #body="{ data }">
                    <span class="font-mono text-sm">{{ formatMeasure(data.measure) }}</span>
                </template>
            </Column>
            <Column :header="trans('properties.endorsements.fields.description')">
                <template #body="{ data }">
                    <span class="whitespace-pre-wrap">{{ data.description }}</span>
                </template>
            </Column>
        </DataTable>
    </div>
</template>
