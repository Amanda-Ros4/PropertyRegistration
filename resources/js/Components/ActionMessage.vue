<script setup>
import { onUnmounted, ref, watch } from 'vue';
import { X } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { AUTO_DISMISS_ALERT_DURATION } from '@/lib/toast-config';

const props = defineProps({
    on: Boolean,
    duration: {
        type: Number,
        default: AUTO_DISMISS_ALERT_DURATION,
    },
});

const visible = ref(false);
let timeoutId = null;

function dismiss() {
    visible.value = false;
    clearTimeout(timeoutId);
}

function scheduleDismiss() {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(dismiss, props.duration);
}

watch(
    () => props.on,
    (value) => {
        if (!value) {
            return;
        }

        visible.value = true;
        scheduleDismiss();
    },
    { immediate: true },
);

onUnmounted(() => {
    clearTimeout(timeoutId);
});
</script>

<template>
    <transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-show="visible"
            class="inline-flex max-w-full items-center gap-3 rounded-md"
            role="status"
        >
            <span class="text-sm text-gray-600 dark:text-gray-400">
                <slot />
            </span>
            <button
                type="button"
                class="shrink-0 rounded-md p-1 text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-200"
                :aria-label="trans('common.close')"
                @click="dismiss"
            >
                <X class="size-4" />
            </button>
        </div>
    </transition>
</template>
