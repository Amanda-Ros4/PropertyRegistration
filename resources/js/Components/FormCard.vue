<script setup>
defineProps({
    title: { type: String, default: null },
});
</script>

<template>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
        <h2 v-if="title" class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ title }}</h2>
        <slot />
    </div>
</template>
