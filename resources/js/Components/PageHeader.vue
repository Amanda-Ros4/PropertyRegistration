<script setup>
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: null },
    createRoute: { type: String, default: null },
    createLabel: { type: String, default: 'New' },
    createIcon: { type: String, default: 'pi pi-plus' },
    backRoute: { type: String, default: null },
    backLabel: { type: String, default: 'Back' },
});
</script>

<template>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <Link v-if="backRoute" :href="route(backRoute)">
                <Button icon="pi pi-arrow-left" text rounded size="small" :aria-label="backLabel" />
            </Link>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ title }}</h1>
                <p v-if="subtitle" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ subtitle }}</p>
            </div>
        </div>

        <Link v-if="createRoute" :href="route(createRoute)">
            <Button :label="createLabel" :icon="createIcon" />
        </Link>
    </div>
</template>
