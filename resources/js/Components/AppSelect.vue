<script setup>
import { computed, ref, watch } from 'vue';
import { X } from '@lucide/vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { cn } from '@/lib/utils';

const model = defineModel({ type: [String, Number, null], default: null });

const props = defineProps({
    options: { type: Array, default: () => [] },
    optionLabel: { type: String, default: 'label' },
    optionValue: { type: String, default: 'value' },
    placeholder: { type: String, default: '' },
    showClear: { type: Boolean, default: false },
    filter: { type: Boolean, default: false },
    invalid: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    class: { type: [String, Object, Array], default: '' },
});

const emit = defineEmits(['change']);

const filterTerm = ref('');

const valueType = computed(() => {
    const sample = props.options.find((option) => option !== null && typeof option === 'object');
    if (!sample) {
        return 'string';
    }
    const value = sample[props.optionValue];
    return typeof value === 'number' ? 'number' : 'string';
});

const normalizedOptions = computed(() =>
    props.options.map((option) => {
        if (option !== null && typeof option === 'object') {
            return {
                label: option[props.optionLabel],
                value: option[props.optionValue],
            };
        }

        return { label: String(option), value: option };
    }),
);

const filteredOptions = computed(() => {
    if (!props.filter || !filterTerm.value.trim()) {
        return normalizedOptions.value;
    }

    const term = filterTerm.value.toLowerCase();
    return normalizedOptions.value.filter((option) =>
        String(option.label ?? '').toLowerCase().includes(term),
    );
});

const selectValue = computed({
    get() {
        if (model.value === null || model.value === undefined || model.value === '') {
            return undefined;
        }

        return String(model.value);
    },
    set(value) {
        if (value === undefined) {
            model.value = null;
            return;
        }

        model.value = valueType.value === 'number' ? Number(value) : value;
    },
});

watch(model, () => emit('change'));
</script>

<template>
    <div :class="cn('relative', props.class)">
        <Select v-model="selectValue" :disabled="disabled">
            <SelectTrigger
                :class="cn(
                    'w-full',
                    showClear && model != null && model !== '' && 'pe-10',
                    invalid && 'border-destructive',
                )"
            >
                <SelectValue :placeholder="placeholder" />
            </SelectTrigger>
            <SelectContent>
                <div v-if="filter" class="p-2 pb-0">
                    <Input
                        v-model="filterTerm"
                        class="h-8"
                        @keydown.stop
                    />
                </div>
                <SelectGroup>
                    <SelectItem
                        v-for="option in filteredOptions"
                        :key="String(option.value)"
                        :value="String(option.value)"
                    >
                        {{ option.label }}
                    </SelectItem>
                </SelectGroup>
            </SelectContent>
        </Select>
        <Button
            v-if="showClear && model != null && model !== ''"
            type="button"
            variant="ghost"
            size="icon-sm"
            class="absolute right-8 top-1/2 z-10 -translate-y-1/2 text-muted-foreground hover:text-foreground"
            @click.stop="model = null"
        >
            <X class="size-3" />
        </Button>
    </div>
</template>
