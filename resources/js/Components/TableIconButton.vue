<script setup>
import { computed } from 'vue';
import { Eye, Pencil, Trash2 } from '@lucide/vue';
import { Button } from '@/Components/ui/button';

const props = defineProps({
    label: { type: String, required: true },
    icon: {
        type: String,
        default: 'eye',
        validator: (value) => ['eye', 'pencil', 'trash'].includes(value),
    },
});

defineEmits(['click']);

const IconComponent = computed(() => {
    if (props.icon === 'trash') {
        return Trash2;
    }

    if (props.icon === 'pencil') {
        return Pencil;
    }

    return Eye;
});

const iconClass = computed(() => (
    props.icon === 'trash' ? 'text-destructive hover:text-destructive' : undefined
));
</script>

<template>
    <Button
        type="button"
        variant="ghost"
        size="icon-sm"
        :class="iconClass"
        :aria-label="label"
        :title="label"
        @click="$emit('click')"
    >
        <component :is="IconComponent" class="size-4" />
    </Button>
</template>
