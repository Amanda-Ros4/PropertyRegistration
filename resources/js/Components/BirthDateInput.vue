<script setup>
import { computed } from 'vue';
import InputText from 'primevue/inputtext';
import DatePicker from 'primevue/datepicker';
import {
    BIRTH_DATE_INPUT_MAX_LENGTH,
    blockNonDigitBeforeInput,
    blockNonDigitKey,
    formatBirthDateInput,
    parseBirthDateInput,
    toBirthDateInputValue,
} from '@/utils/formatting';

const model = defineModel({ type: String, default: '' });

defineProps({
    invalid: { type: Boolean, default: false },
    placeholder: { type: String, default: '' },
});

defineEmits(['blur']);

const pickerValue = computed(() => parseBirthDateInput(model.value));

function syncMasked(value) {
    const formatted = formatBirthDateInput(value);
    if (formatted === model.value) {
        model.value = `${formatted}\u200b`;
        queueMicrotask(() => {
            model.value = formatted;
        });
        return;
    }
    model.value = formatted;
}

function onPicked(date) {
    model.value = date ? toBirthDateInputValue(date) : '';
}
</script>

<template>
    <div
        class="relative w-full"
        @keydown.capture="blockNonDigitKey"
        @beforeinput.capture="blockNonDigitBeforeInput"
    >
        <InputText
            :modelValue="model"
            :placeholder="placeholder"
            :invalid="invalid"
            class="w-full font-mono pe-10"
            inputmode="numeric"
            :maxlength="BIRTH_DATE_INPUT_MAX_LENGTH"
            @update:model-value="syncMasked"
            @blur="$emit('blur')"
        />
        <DatePicker
            :modelValue="pickerValue"
            :maxDate="new Date()"
            :manualInput="false"
            showIcon
            iconDisplay="button"
            dateFormat="dd/mm/yy"
            class="birth-date-single-picker"
            @update:model-value="onPicked"
        />
    </div>
</template>

<style scoped>
.birth-date-single-picker {
    position: absolute;
    inset-inline-end: 0.15rem;
    top: 50%;
    transform: translateY(-50%);
}

.birth-date-single-picker :deep(input) {
    display: none !important;
    width: 0 !important;
    min-width: 0 !important;
    padding: 0 !important;
    border: 0 !important;
    position: absolute !important;
}

.birth-date-single-picker :deep(button) {
    border: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    width: 2.25rem;
    height: 2.25rem;
}
</style>
