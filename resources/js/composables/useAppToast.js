import { toast } from 'vue-sonner';
import { trans } from 'laravel-vue-i18n';

const DEFAULT_SUCCESS_DURATION = 4500;
const DEFAULT_WARN_DURATION = 5500;
const DEFAULT_ERROR_DURATION = 6500;

export function useAppToast() {
    function addErrorToast(detail, summary = null) {
        toast.error(summary ?? trans('toast.error_summary'), {
            description: detail,
            duration: DEFAULT_ERROR_DURATION,
        });
    }

    function addWarnToast(detail, summary = null) {
        toast.warning(summary ?? trans('toast.warn_summary'), {
            description: detail,
            duration: DEFAULT_WARN_DURATION,
        });
    }

    function addSuccessToast(detail, summary = null) {
        toast.success(summary ?? trans('toast.success_summary'), {
            description: detail,
            duration: DEFAULT_SUCCESS_DURATION,
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
