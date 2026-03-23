import { i18nVue } from 'laravel-vue-i18n';

const LOCALE_KEY = 'app_locale';
/** Matches typical APP_LOCALE for this app when env is unset in production builds. */
const DEFAULT_LOCALE = 'pt_BR';
const SUPPORTED_LOCALES = ['en', 'pt_BR', 'es'];
/** JSON file used when the active locale file is missing keys or does not exist. */
const FALLBACK_TRANSLATION_LANG = 'en';

function readCookie(name) {
    if (typeof document === 'undefined') return null;
    const escaped = name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1');
    const match = document.cookie.match(new RegExp(`(?:^|; )${escaped}=([^;]*)`));
    return match ? decodeURIComponent(match[1]) : null;
}

function localeFromHtmlLang() {
    if (typeof document === 'undefined') return null;
    const raw = document.documentElement?.lang?.trim();
    if (!raw) return null;
    const lower = raw.toLowerCase();
    if (lower === 'pt-br') return 'pt_BR';
    if (lower === 'en' || lower.startsWith('en-')) return 'en';
    if (lower === 'es' || lower.startsWith('es-')) return 'es';
    return null;
}

export function getStoredLocale() {
    if (typeof window === 'undefined') return DEFAULT_LOCALE;
    const stored = localStorage.getItem(LOCALE_KEY);
    if (stored && SUPPORTED_LOCALES.includes(stored)) return stored;
    const fromCookie = readCookie('app_locale');
    if (fromCookie && SUPPORTED_LOCALES.includes(fromCookie)) return fromCookie;
    const fromHtml = localeFromHtmlLang();
    if (fromHtml) return fromHtml;
    return DEFAULT_LOCALE;
}

const COOKIE_MAX_AGE = 365 * 24 * 60 * 60; // 1 year

export function storeLocale(locale) {
    if (typeof window !== 'undefined') {
        localStorage.setItem(LOCALE_KEY, locale);
        document.cookie = `app_locale=${locale}; path=/; max-age=${COOKIE_MAX_AGE}; SameSite=Lax`;
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
    const fallbackKey = `../../../lang/${FALLBACK_TRANSLATION_LANG}.json`;
    const module = langs[key] ?? langs[fallbackKey];
    return module?.default ?? {};
}

export function installI18n(app, initialLocale = DEFAULT_LOCALE) {
    app.use(i18nVue, {
        lang: initialLocale,
        fallbackLang: FALLBACK_TRANSLATION_LANG,
        fallbackMissingTranslations: true,
        resolve: resolveTranslations,
    });
}
