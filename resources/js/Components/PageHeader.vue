<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
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

const visitBack = () => {
    if (props.backRoute) {
        router.visit(route(props.backRoute));
    } else {
        router.back();
    }
};
</script>

<template>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <SecondaryButton
                v-if="backRoute"
                type="button"
                :aria-label="backLabel"
                @click="visitBack"
            >
                <component :is="BackIcon" class="size-4" />
            </SecondaryButton>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ title }}</h1>
                <p v-if="subtitle" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ subtitle }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <slot name="actions" />
            <PrimaryButton
                v-if="createRoute"
                type="button"
                class="gap-2"
                @click="router.visit(route(createRoute))"
            >
                <component :is="CreateIcon" class="size-4" />
                {{ createLabel }}
            </PrimaryButton>
        </div>
    </div>
</template>
