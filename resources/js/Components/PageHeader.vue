<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { resolveAppIcon } from '@/lib/app-icons';

const props = defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: null },
    createRoute: { type: String, default: null },
    createLabel: { type: String, default: 'New' },
    createIcon: { type: String, default: 'plus' },
    backRoute: { type: String, default: null },
    backLabel: { type: String, default: 'Back' },
});

const CreateIcon = computed(() => resolveAppIcon(props.createIcon));
const BackIcon = computed(() => resolveAppIcon('arrow-left'));
</script>

<template>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <Link v-if="backRoute" :href="route(backRoute)">
                <Button variant="ghost" size="icon-sm" :aria-label="backLabel">
                    <component :is="BackIcon" class="size-4" />
                </Button>
            </Link>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ title }}</h1>
                <p v-if="subtitle" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ subtitle }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <slot name="actions" />
            <Link v-if="createRoute" :href="route(createRoute)">
                <Button>
                    <component :is="CreateIcon" class="size-4" />
                    {{ createLabel }}
                </Button>
            </Link>
        </div>
    </div>
</template>
