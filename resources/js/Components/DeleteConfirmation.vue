<script setup>
import { useConfirm } from 'primevue/useconfirm';
import { trans } from 'laravel-vue-i18n';

const props = defineProps({
    title: { type: String, default: () => trans('common.confirm') },
    message: { type: String, default: () => trans('common.confirm') },
    acceptLabel: { type: String, default: () => trans('common.yes') },
    rejectLabel: { type: String, default: () => trans('common.cancel') },
});

const emit = defineEmits(['confirm']);
const confirm = useConfirm();

function open() {
    confirm.require({
        header: props.title,
        message: props.message,
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: props.acceptLabel,
        rejectLabel: props.rejectLabel,
        acceptClass: 'p-button-danger',
        accept: () => emit('confirm'),
    });
}

defineExpose({ open });
</script>

<template>
    <span />
</template>
