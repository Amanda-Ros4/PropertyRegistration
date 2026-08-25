<script setup>
import { ref } from 'vue';
import { trans } from 'laravel-vue-i18n';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/Components/ui/alert-dialog';

const props = defineProps({
    title: { type: String, default: '' },
    message: { type: String, default: '' },
    acceptLabel: { type: String, default: '' },
    rejectLabel: { type: String, default: '' },
});

const emit = defineEmits(['confirm']);

const open = ref(false);

function openDialog() {
    open.value = true;
}

function onConfirm() {
    emit('confirm');
    open.value = false;
}

defineExpose({ open: openDialog });
</script>

<template>
    <AlertDialog v-model:open="open">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ title || trans('common.confirm') }}</AlertDialogTitle>
                <AlertDialogDescription>{{ message || trans('common.confirm') }}</AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>{{ rejectLabel || trans('common.cancel') }}</AlertDialogCancel>
                <AlertDialogAction
                    class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                    @click="onConfirm"
                >
                    {{ acceptLabel || trans('common.delete') }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
