<script setup>
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { Loader2, Plus } from '@lucide/vue';
import FormField from '@/Components/FormField.vue';
import AppSelect from '@/Components/AppSelect.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { cn } from '@/lib/utils';
import {
    blockNonAreaBeforeInput,
    blockNonAreaKey,
    formatAreaInput,
} from '@/utils/formatting';
import { useAppToast } from '@/composables/useAppToast';
import { usePrecognitiveForm } from '@/composables/usePrecognitiveForm';

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

const { form, validateField } = usePrecognitiveForm('post', route('properties.endorsements.store', props.propertyId), {
    event: 'O',
    measure: '',
    description: '',
    description_maxlength: 1000,
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
    form.submit({
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
                <AppSelect
                    v-model="form.event"
                    :options="eventOptions"
                    :placeholder="trans('properties.endorsements.placeholders.event')"
                    :invalid="!!form.errors.event"
                    class="w-full"
                    @change="() => { form.clearErrors('event'); validateField('event'); validateField('measure'); }"
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
                    <Input
                        :model-value="form.measure"
                        inputmode="decimal"
                        :placeholder="trans('properties.endorsements.placeholders.measure')"
                        :class="cn('w-full', form.errors.measure && 'border-destructive')"
                        @update:model-value="onMeasureInput"
                        @blur="validateField('measure')"
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
                    :maxlength="form.description_maxlength"
                    :placeholder="trans('properties.endorsements.placeholders.description')"
                    :class="cn('w-full', form.errors.description && 'border-destructive')"
                    @blur="validateField('description')"
                    @change="form.clearErrors('description')"
                />
            </FormField>

            <div class="md:col-span-2 flex justify-end">
                <PrimaryButton
                    type="submit"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ trans('properties.endorsements.save') }}
                </PrimaryButton>
            </div>
        </form>

        <EmptyState
            v-if="endorsements.length === 0"
            icon="file-edit"
            :title="trans('properties.endorsements.empty')"
            :description="trans('properties.endorsements.empty_description')"
        />

        <div
            v-else
            class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800"
        >
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-[120px]">
                            {{ trans('properties.endorsements.fields.date') }}
                        </TableHead>
                        <TableHead>
                            {{ trans('properties.endorsements.fields.event') }}
                        </TableHead>
                        <TableHead class="w-[140px]">
                            {{ trans('properties.endorsements.fields.measure') }}
                        </TableHead>
                        <TableHead>
                            {{ trans('properties.endorsements.fields.description') }}
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="endorsement in endorsements" :key="endorsement.id">
                        <TableCell>
                            <span class="text-sm">{{ formatDate(endorsement.occurred_on) }}</span>
                        </TableCell>
                        <TableCell>
                            {{ trans(eventLabelKey[endorsement.event?.value ?? endorsement.event] || eventLabelKey.O) }}
                        </TableCell>
                        <TableCell>
                            <span class="text-sm">{{ formatMeasure(endorsement.measure) }}</span>
                        </TableCell>
                        <TableCell>
                            <span class="whitespace-pre-wrap">{{ endorsement.description }}</span>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
