<script setup>
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';

defineProps({
    icon: { type: String, default: 'pi pi-inbox' },
    title: { type: String, required: true },
    description: { type: String, default: null },
    actionLabel: { type: String, default: null },
    actionRoute: { type: String, default: null },
    actionIcon: { type: String, default: 'pi pi-plus' },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
            <i :class="[icon, 'text-2xl text-gray-400 dark:text-gray-500']" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ title }}</h3>
        <p v-if="description" class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mb-6">{{ description }}</p>
        <Link v-if="actionRoute" :href="route(actionRoute)">
            <Button :label="actionLabel" :icon="actionIcon" />
        </Link>
    </div>
</template>
