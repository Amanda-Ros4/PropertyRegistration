import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { installPrimeVue } from '@/plugins/primevue';
import { installI18n, getStoredLocale, storeLocale } from '@/plugins/i18n';

const appNames = {
    en: 'Property Registration',
    pt_BR: 'Cadastro Imobiliário',
    es: 'Registro de Propiedades',
};

function getAppName(locale) {
    return appNames[locale] || appNames.pt_BR;
}

createInertiaApp({
    title: (title) => {
        const appName = getAppName(getStoredLocale());
        return title ? `${title} - ${appName}` : appName;
    },
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    defaults: {
        visitOptions: (_href, options) => {
            const locale = getStoredLocale();

            return {
                ...options,
                headers: {
                    ...options.headers,
                    'X-Locale': locale,
                },
            };
        },
    },
    setup({ el, App, props, plugin }) {
        const locale = getStoredLocale();
        storeLocale(locale);
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(ZiggyVue);
        installPrimeVue(app);
        installI18n(app, locale);

        return app.mount(el);
    },
    progress: {
        color: '#6366f1',
    },
});
