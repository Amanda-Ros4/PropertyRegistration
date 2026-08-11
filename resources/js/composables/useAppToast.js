import { useToast } from 'primevue/usetoast';
import { trans } from 'laravel-vue-i18n';

const DEFAULT_SUCCESS_LIFE = 4500;
const DEFAULT_WARN_LIFE = 5500;
const DEFAULT_ERROR_LIFE = 6500;

export function useAppToast() {
    const toast = useToast();

    function addErrorToast(detail, summary = null) {
        toast.add({
            severity: 'error',
            summary: summary ?? trans('toast.error_summary'),
            detail,
            life: DEFAULT_ERROR_LIFE,
            closable: true,
        });
    }

    function addWarnToast(detail, summary = null) {
        toast.add({
            severity: 'warn',
            summary: summary ?? trans('toast.warn_summary'),
            detail,
            life: DEFAULT_WARN_LIFE,
            closable: true,
        });
    }

    function addSuccessToast(detail, summary = null) {
        toast.add({
            severity: 'success',
            summary: summary ?? trans('toast.success_summary'),
            detail,
            life: DEFAULT_SUCCESS_LIFE,
            closable: true,
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

    return {
        addErrorToast,
        addWarnToast,
        addSuccessToast,
        showValidationErrorToast,
    };
}
