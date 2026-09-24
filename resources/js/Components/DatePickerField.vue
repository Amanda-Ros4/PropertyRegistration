<script setup>
import { computed, ref, watch } from 'vue';
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

// Estado local para controlar o que está digitado no campo de texto
const inputValue = ref(formatToDateString(model.value));

function toCalendarDate(date) {
    if (!date) return undefined;
    return new CalendarDate(date.getFullYear(), date.getMonth() + 1, date.getDate());
}

function fromCalendarDate(value) {
    if (!value) return null;
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

// Transforma objeto Date em string DD/MM/YYYY
function formatToDateString(date) {
    if (!date) return '';
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    return `${day}/${month}/${date.getFullYear()}`;
}

// Quando a data muda, atualiza o campo de texto
watch(() => model.value, (newDate) => {
    const formatted = formatToDateString(newDate);
    if (inputValue.value !== formatted) {
        inputValue.value = formatted;
    }
});

// Máscara e validação durante a digitação
function handleInput(e) {
    // Remove tudo que não for número
    let val = e.target.value.replace(/\D/g, '');
    
    // Adiciona as barras automaticamente
    if (val.length > 2) val = val.slice(0, 2) + '/' + val.slice(2);
    if (val.length > 5) val = val.slice(0, 5) + '/' + val.slice(5, 9);
    
    inputValue.value = val;

    if (val.length === 10) {
        const [day, month, year] = val.split('/');
        const parsedDate = new Date(`${year}-${month}-${day}T12:00:00`);
        
        // Verifica se é uma data válida antes de emitir
        if (!isNaN(parsedDate.getTime())) {
            model.value = parsedDate;
            emit('date-select');
        }
    } else if (val.length === 0) {
        model.value = null;
        emit('clear-click');
    }
}

function clearDate() {
    model.value = null;
    inputValue.value = '';
    emit('clear-click');
}
</script>

<template>
    <div :class="cn('relative w-full', props.class)">
        <Popover>
            <PopoverTrigger as-child>
                
                <div
                    :class="cn(
                        'flex h-9 w-full items-center rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-within:ring-1 focus-within:ring-ring cursor-text',
                        showClear && model && 'pe-10'
                    )"
                >
                    <CalendarIcon class="mr-2 size-4 shrink-0 opacity-70 cursor-pointer" />
                    
                    <input
                        v-model="inputValue"
                        type="text"
                        maxlength="10"
                        :placeholder="props.placeholder"
                        class="w-full bg-transparent p-0 border-none outline-none focus:ring-0 placeholder:text-muted-foreground"
                        @input="handleInput"
                    />
                </div>
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