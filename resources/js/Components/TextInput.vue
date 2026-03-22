<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
    maxlength: [String, Number],
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        class="border-slate-300 focus:border-green-600 focus:ring-green-500 rounded-md shadow-sm"
        :value="modelValue ?? ''"
        :maxlength="maxlength"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>
