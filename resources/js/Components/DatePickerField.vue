<script setup>
import { computed } from 'vue';
import { CalendarDate, getLocalTimeZone } from '@internationalized/date';
import { CalendarIcon, X } from '@lucide/vue';
import { Button } from '@/Components/ui/button';
import { Calendar } from '@/Components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { cn } from '@/lib/utils';

const model = defineModel({ type: Date, default: null });

const props = defineProps({
    placeholder: { type: String, default: '' },
    showClear: { type: Boolean, default: false },
    maxDate: { type: Date, default: null },
    class: { type: [String, Object, Array], default: '' },
});

const emit = defineEmits(['date-select', 'clear-click']);

function toCalendarDate(date) {
    if (!date) {
        return undefined;
    }

    return new CalendarDate(date.getFullYear(), date.getMonth() + 1, date.getDate());
}

function fromCalendarDate(value) {
    if (!value) {
        return null;
    }

    return value.toDate(getLocalTimeZone());
}

const calendarValue = computed({
    get: () => toCalendarDate(model.value),
    set: (value) => {
        model.value = fromCalendarDate(value);
        emit('date-select');
    },
});

const maxValue = computed(() => toCalendarDate(props.maxDate));

const displayLabel = computed(() => {
    if (!model.value) {
        return props.placeholder;
    }

    const day = String(model.value.getDate()).padStart(2, '0');
    const month = String(model.value.getMonth() + 1).padStart(2, '0');
    return `${day}/${month}/${model.value.getFullYear()}`;
});

function clearDate() {
    model.value = null;
    emit('clear-click');
}
</script>

<template>
    <div :class="cn('relative w-full', props.class)">
        <Popover>
            <PopoverTrigger as-child>
                <Button
                    variant="outline"
                    :class="cn(
                        'h-9 w-full justify-start font-normal',
                        showClear && model && 'pe-10',
                    )"
                >
                    <CalendarIcon class="mr-2 size-4 shrink-0 opacity-70" />
                    <span :class="cn('truncate', !model && 'text-muted-foreground')">{{ displayLabel }}</span>
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0" align="start">
                <Calendar v-model="calendarValue" :max-value="maxValue" />
            </PopoverContent>
        </Popover>
        <Button
            v-if="showClear && model"
            type="button"
            variant="ghost"
            size="icon-sm"
            class="absolute right-1 top-1/2 z-10 -translate-y-1/2 text-muted-foreground hover:text-foreground"
            @click="clearDate"
        >
            <X class="size-3" />
        </Button>
    </div>
</template>
