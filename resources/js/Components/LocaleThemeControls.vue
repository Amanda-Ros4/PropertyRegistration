<script setup>
import { computed, onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { usePrimeVue } from 'primevue/config';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import { useTheme } from '@/composables/useTheme';
import { useLocale, SUPPORTED_LOCALES, primeVueLocales } from '@/composables/useLocale';

defineProps({
    showLocaleLabel: { type: Boolean, default: true },
});

const primeVue = usePrimeVue();
const { isDark, toggleTheme } = useTheme();
const { currentLocale, setLocale } = useLocale();
const langMenuRef = ref(null);

const langMenuItems = computed(() =>
    SUPPORTED_LOCALES.map((locale) => ({
        label: locale.label,
        command: () => changeLocale(locale.code),
    })),
);

const currentLocaleLabel = computed(
    () => SUPPORTED_LOCALES.find((locale) => locale.code === currentLocale.value)?.label ?? currentLocale.value,
);

onMounted(() => {
    syncPrimeVueLocale(currentLocale.value);
});

function syncPrimeVueLocale(locale) {
    if (primeVueLocales[locale]) {
        primeVue.config.locale = { ...primeVue.config.locale, ...primeVueLocales[locale] };
    }
}

async function changeLocale(locale) {
    await setLocale(locale);
    syncPrimeVueLocale(locale);
    router.reload({
        preserveState: true,
        preserveScroll: true,
        headers: {
            'X-Locale': locale,
        },
    });
}
</script>

<template>
    <div class="flex items-center gap-2">
        <Button
            icon="pi pi-language"
            text
            rounded
            :label="showLocaleLabel ? currentLocaleLabel : undefined"
            class="!text-slate-600 dark:!text-slate-400"
            :aria-label="trans('language.label')"
            @click="(event) => langMenuRef.toggle(event)"
        />
        <Menu ref="langMenuRef" :model="langMenuItems" :popup="true" />

        <Button
            :icon="isDark ? 'pi pi-sun' : 'pi pi-moon'"
            text
            rounded
            :aria-label="isDark ? trans('theme.light') : trans('theme.dark')"
            class="!text-slate-600 dark:!text-slate-400"
            @click="toggleTheme"
        />
    </div>
</template>
