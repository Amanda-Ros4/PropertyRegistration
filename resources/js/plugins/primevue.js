import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';
import { definePreset } from '@primevue/themes';

const PropertyTheme = definePreset(Aura, {
    semantic: {
        primary: {
            50: '{green.50}',
            100: '{green.100}',
            200: '{green.200}',
            300: '{green.300}',
            400: '{green.400}',
            500: '{green.500}',
            600: '{green.600}',
            700: '{green.700}',
            800: '{green.800}',
            900: '{green.900}',
            950: '{green.950}',
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
