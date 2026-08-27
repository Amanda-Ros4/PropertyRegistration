<script setup>
import { computed } from 'vue';
import { Badge } from '@/Components/ui/badge';
import { cn } from '@/lib/utils';

const props = defineProps({
    value: { type: String, default: '' },
    severity: { type: String, default: 'secondary' },
});

const variant = computed(() => {
    const map = {
        info: 'default',
        success: 'secondary',
        secondary: 'secondary',
        danger: 'destructive',
        warn: 'outline',
        warning: 'outline',
    };

    return map[props.severity] ?? 'secondary';
});

const extraClass = computed(() => {
    if (props.value === 'Inactive' || props.value === 'Inativo') {
        return 'border-transparent bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
    }

    if (props.severity === 'success') {
        return 'border-transparent bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
    }

    if (props.severity === 'info') {
        return 'border-transparent bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
    }

    if (props.severity === 'warn' || props.severity === 'warning') {
        return 'border-transparent bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300';
    }

    if (props.severity === 'danger' || props.severity === 'destructive') {
        return 'border-transparent bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300';
    }

    return '';
});
</script>

<template>
    <Badge :variant="variant" :class="cn(extraClass)">
        {{ value }}
    </Badge>
</template>
