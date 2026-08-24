<script setup>
defineProps({
    title: { type: String, default: null },
});
</script>

<template>
    <div class="mb-6 rounded-xl border border-border bg-card text-card-foreground p-4 shadow-sm space-y-3">
        <p
            v-if="title"
            class="text-sm font-semibold text-foreground"
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
