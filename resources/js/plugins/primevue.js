import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';
import { definePreset } from '@primevue/themes';

const PropertyTheme = definePreset(Aura, {
    semantic: {
        primary: {
            50: '{indigo.50}',
            100: '{indigo.100}',
            200: '{indigo.200}',
            300: '{indigo.300}',
            400: '{indigo.400}',
            500: '{indigo.500}',
            600: '{indigo.600}',
            700: '{indigo.700}',
            800: '{indigo.800}',
            900: '{indigo.900}',
            950: '{indigo.950}',
        },
    },
});

export function installPrimeVue(app) {
    app.use(PrimeVue, {
        theme: {
            preset: PropertyTheme,
            options: {
                darkModeSelector: '.dark',
                cssLayer: false,
            },
        },
        ripple: true,
    });
    app.use(ConfirmationService);
    app.use(ToastService);
}
