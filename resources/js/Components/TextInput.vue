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
        class="jetstream-text-input rounded-md shadow-sm
            border border-slate-300
            !bg-white !text-gray-900 !placeholder:text-slate-400
            focus:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-0
            dark:border-slate-600
            dark:!bg-slate-800 dark:!text-slate-100 dark:!placeholder:text-slate-500
            dark:focus:border-green-500 dark:focus:ring-green-600
            dark:[color-scheme:dark]"
        :value="modelValue ?? ''"
        :maxlength="maxlength"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>
