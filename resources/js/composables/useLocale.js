import { ref } from 'vue';
import { loadLanguageAsync } from 'laravel-vue-i18n';
import { getStoredLocale, storeLocale } from '@/plugins/i18n';

export const SUPPORTED_LOCALES = [
    { code: 'en', label: 'English' },
    { code: 'pt_BR', label: 'Português' },
    { code: 'es', label: 'Español' },
];

export function useLocale() {
    const currentLocale = ref(
        typeof window !== 'undefined' ? getStoredLocale() : 'pt_BR',
    );

    async function setLocale(locale) {
        await loadLanguageAsync(locale);
        storeLocale(locale);
        currentLocale.value = locale;
    }

    return { currentLocale, setLocale, SUPPORTED_LOCALES };
}
