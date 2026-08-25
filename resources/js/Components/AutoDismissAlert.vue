<script setup>
import { onUnmounted, ref, watch } from 'vue';
import { X } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { AUTO_DISMISS_ALERT_DURATION } from '@/lib/toast-config';

const props = defineProps({
    message: {
        type: String,
        default: '',
    },
    variant: {
        type: String,
        default: 'success',
    },
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
    () => props.message,
    (message) => {
        if (!message) {
            dismiss();
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

const variantClass = {
    success: 'text-green-600 dark:text-green-400',
    error: 'text-red-600 dark:text-red-400',
    warning: 'text-amber-600 dark:text-amber-400',
    info: 'text-slate-600 dark:text-slate-300',
};
</script>

<template>
    <transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 -translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-1"
    >
        <div
            v-if="visible && message"
            class="mb-4 flex items-start justify-between gap-3 rounded-md"
            :class="variantClass[variant] ?? variantClass.success"
            role="status"
        >
            <p class="flex-1 font-medium text-sm leading-relaxed">
                {{ message }}
            </p>
            <button
                type="button"
                class="shrink-0 rounded-md p-1 text-current/70 hover:text-current focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                :aria-label="trans('common.close')"
                @click="dismiss"
            >
                <X class="size-4" />
            </button>
        </div>
    </transition>
</template>
