<script setup>
defineProps({
    title: { type: String, default: null },
});
</script>

<template>
    <div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 text-slate-900 dark:text-slate-100 p-4 shadow-sm space-y-3">
        <p
            v-if="title"
            class="text-sm font-semibold text-slate-900 dark:text-slate-100"
        >
            {{ title }}
        </p>

        <slot />

        <div
            v-if="$slots.actions"
            class="flex justify-end"
        >
            <slot name="actions" />
        </div>
    </div>
</template>
