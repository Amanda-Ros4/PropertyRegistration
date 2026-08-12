import { useForm } from 'laravel-precognition-vue-inertia';

const VALIDATION_DEBOUNCE_MS = 400;

/**
 * Formulário Inertia com validação ao vivo via Laravel Precognition.
 */
export function usePrecognitiveForm(method, url, data) {
    const form = useForm(method, url, data);

    form.setValidationTimeout(VALIDATION_DEBOUNCE_MS);

    function validateField(field) {
        form.validate(field);
    }

    return { form, validateField };
}
