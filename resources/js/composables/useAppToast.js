import { toast } from 'vue-sonner';
import { trans } from 'laravel-vue-i18n';
import { TOAST_DURATION } from '@/lib/toast-config';

function summaryForFlashType(type) {
    if (type === 'error') {
        return trans('toast.error_summary');
    }

    if (type === 'warn' || type === 'warning') {
        return trans('toast.warn_summary');
    }

    if (type === 'info') {
        return trans('toast.info_summary');
    }

    return trans('toast.success_summary');
}

function durationForFlashType(type) {
    if (type === 'error') {
        return TOAST_DURATION.error;
    }

    if (type === 'warn' || type === 'warning') {
        return TOAST_DURATION.warning;
    }

    if (type === 'info') {
        return TOAST_DURATION.info;
    }

    return TOAST_DURATION.success;
}

export function useAppToast() {
    function addErrorToast(detail, summary = null) {
        toast.error(summary ?? trans('toast.error_summary'), {
            description: detail,
            duration: TOAST_DURATION.error,
        });
    }

    function addWarnToast(detail, summary = null) {
        toast.warning(summary ?? trans('toast.warn_summary'), {
            description: detail,
            duration: TOAST_DURATION.warning,
        });
    }

    function addSuccessToast(detail, summary = null) {
        toast.success(summary ?? trans('toast.success_summary'), {
            description: detail,
            duration: TOAST_DURATION.success,
        });
    }

    function showValidationErrorToast(errors) {
        const keys = errors && typeof errors === 'object' ? Object.keys(errors) : [];
        if (!keys.length) {
            addErrorToast(trans('toast.validation_generic'), trans('toast.validation_summary'));
            return;
        }
        const first = errors[keys[0]];
        const msg = Array.isArray(first) ? first[0] : String(first);
        addErrorToast(msg, trans('toast.validation_summary'));
    }

    function showFlashToast(flash, lastFlashIdRef = null) {
        if (!flash?.message) {
            return;
        }

        const flashId = flash.id ?? `${flash.type}:${flash.message}`;
        if (lastFlashIdRef?.value === flashId) {
            return;
        }

        if (lastFlashIdRef) {
            lastFlashIdRef.value = flashId;
        }

        const summary = summaryForFlashType(flash.type);
        const duration = durationForFlashType(flash.type);
        const options = { description: flash.message, duration };

        if (flash.type === 'error') {
            toast.error(summary, options);
            return;
        }

        if (flash.type === 'warn' || flash.type === 'warning') {
            toast.warning(summary, options);
            return;
        }

        if (flash.type === 'info') {
            toast.info(summary, options);
            return;
        }

        toast.success(summary, options);
    }

    return {
        addErrorToast,
        addWarnToast,
        addSuccessToast,
        showValidationErrorToast,
        showFlashToast,
    };
}
