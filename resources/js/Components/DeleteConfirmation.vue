<script setup>
import { useConfirm } from 'primevue/useconfirm';
import { trans } from 'laravel-vue-i18n';

const props = defineProps({
    title: { type: String, default: '' },
    message: { type: String, default: '' },
    acceptLabel: { type: String, default: '' },
    rejectLabel: { type: String, default: '' },
});

const emit = defineEmits(['confirm']);
const confirm = useConfirm();

function open() {
    confirm.require({
        header: props.title || trans('common.confirm'),
        message: props.message || trans('common.confirm'),
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: props.acceptLabel || trans('common.delete'),
        rejectLabel: props.rejectLabel || trans('common.cancel'),
        acceptClass: 'p-button-danger',
        accept: () => emit('confirm'),
    });
}

defineExpose({ open });
</script>

<template>
    <span />
</template>
