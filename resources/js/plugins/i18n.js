import { i18nVue } from 'laravel-vue-i18n';

const LOCALE_KEY = 'app_locale';
const DEFAULT_LOCALE = 'en';
const SUPPORTED_LOCALES = ['en', 'pt_BR', 'es'];

export function getStoredLocale() {
    if (typeof window === 'undefined') return DEFAULT_LOCALE;
    const stored = localStorage.getItem(LOCALE_KEY);
    return stored && SUPPORTED_LOCALES.includes(stored) ? stored : DEFAULT_LOCALE;
}

export function storeLocale(locale) {
    if (typeof window !== 'undefined') {
        localStorage.setItem(LOCALE_KEY, locale);
    }
}

/**
 * Resolves translation files eagerly for SSR compatibility.
 * Per laravel-vue-i18n docs: resolve must return the module .default (not a Promise).
 * @see https://github.com/xiCO2k/laravel-vue-i18n#ssr-server-side-rendering
 */
function resolveTranslations(lang) {
    const langs = import.meta.glob('../../../lang/*.json', { eager: true });
    const key = `../../../lang/${lang}.json`;
    const fallbackKey = `../../../lang/${DEFAULT_LOCALE}.json`;
    const module = langs[key] ?? langs[fallbackKey];
    return module?.default ?? {};
}

export function installI18n(app, initialLocale = DEFAULT_LOCALE) {
    app.use(i18nVue, {
        lang: initialLocale,
        fallbackLang: DEFAULT_LOCALE,
        fallbackMissingTranslations: true,
        resolve: resolveTranslations,
    });
}
