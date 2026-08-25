<script setup>
import { computed, ref, watch } from 'vue';
import { CalendarDate, getLocalTimeZone, today } from '@internationalized/date';
import { CalendarIcon } from '@lucide/vue';
import { Button } from '@/Components/ui/button';
import { Calendar } from '@/Components/ui/calendar';
import { Input } from '@/Components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { cn } from '@/lib/utils';
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

const popoverOpen = ref(false);
const maxDate = today(getLocalTimeZone());

const calendarValue = computed({
    get() {
        const parsed = parseBirthDateInput(model.value);
        if (!parsed) {
            return undefined;
        }

        return new CalendarDate(parsed.getFullYear(), parsed.getMonth() + 1, parsed.getDate());
    },
    set(value) {
        model.value = value ? toBirthDateInputValue(value.toDate(getLocalTimeZone())) : '';
        popoverOpen.value = false;
    },
});

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
</script>

<template>
    <div
        class="relative w-full"
        @keydown.capture="blockNonDigitKey"
        @beforeinput.capture="blockNonDigitBeforeInput"
    >
        <Input
            :model-value="model"
            :placeholder="placeholder"
            :class="cn('w-full pe-10', invalid && 'border-destructive')"
            inputmode="numeric"
            :maxlength="BIRTH_DATE_INPUT_MAX_LENGTH"
            @update:model-value="syncMasked"
            @blur="$emit('blur')"
        />
        <Popover v-model:open="popoverOpen">
            <PopoverTrigger as-child>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    class="absolute right-1 top-1/2 -translate-y-1/2"
                >
                    <CalendarIcon class="size-4" />
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0" align="end">
                <Calendar v-model="calendarValue" :max-value="maxDate" />
            </PopoverContent>
        </Popover>
    </div>
</template>
