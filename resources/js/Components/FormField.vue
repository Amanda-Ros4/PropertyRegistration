<script setup>
defineProps({
    label: { type: String, required: true },
    error: { type: String, default: null },
    required: { type: Boolean, default: false },
    hint: { type: String, default: null },
});
</script>

<template>
    <div class="flex flex-col gap-1.5">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ label }}
            <span v-if="required" class="text-red-500 ml-0.5">*</span>
        </label>
        <slot />
        <small v-if="hint && !error" class="text-gray-500 dark:text-gray-400 text-xs">{{ hint }}</small>
        <small v-if="error" class="text-red-500 dark:text-red-400 text-xs">{{ error }}</small>
    </div>
</template>
