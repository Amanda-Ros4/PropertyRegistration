<script setup>
import { computed, onMounted, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { usePrimeVue } from 'primevue/config';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import { useTheme } from '@/composables/useTheme';
import { useLocale, SUPPORTED_LOCALES, primeVueLocales } from '@/composables/useLocale';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const page = usePage();
const primeVue = usePrimeVue();
const { isDark, toggleTheme } = useTheme();
const { currentLocale, setLocale } = useLocale();
const langMenuRef = ref(null);

onMounted(() => {
    const locale = currentLocale.value;

    if (primeVueLocales[locale]) {
        primeVue.config.locale = { ...primeVue.config.locale, ...primeVueLocales[locale] };
    }
});

async function changeLocale(locale) {
    await setLocale(locale);

    if (primeVueLocales[locale]) {
        primeVue.config.locale = { ...primeVue.config.locale, ...primeVueLocales[locale] };
    }

    router.reload({
        preserveState: true,
        preserveScroll: true,
        headers: {
            'X-Locale': locale,
        },
    });
}

const langMenuItems = ref(
    SUPPORTED_LOCALES.map((locale) => ({
        label: locale.label,
        command: () => changeLocale(locale.code),
    })),
);

const heroCards = computed(() => [
    {
        icon: 'pi pi-users',
        title: trans('welcome.cards.people_title'),
        description: trans('welcome.cards.people_description'),
    },
    {
        icon: 'pi pi-building',
        title: trans('welcome.cards.properties_title'),
        description: trans('welcome.cards.properties_description'),
    },
    {
        icon: 'pi pi-language',
        title: trans('welcome.cards.languages_title'),
        description: trans('welcome.cards.languages_description'),
    },
]);

const benefits = computed(() => [
    {
        icon: 'pi pi-shield',
        title: trans('welcome.benefits.secure_title'),
        description: trans('welcome.benefits.secure_description'),
    },
    {
        icon: 'pi pi-bolt',
        title: trans('welcome.benefits.fast_title'),
        description: trans('welcome.benefits.fast_description'),
    },
    {
        icon: 'pi pi-desktop',
        title: trans('welcome.benefits.responsive_title'),
        description: trans('welcome.benefits.responsive_description'),
    },
]);
</script>

<template>
    <Head :title="trans('welcome.title')" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 overflow-hidden">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-28 -left-24 h-80 w-80 rounded-full bg-indigo-500/15 blur-3xl dark:bg-indigo-500/20" />
            <div class="absolute top-1/3 -right-24 h-96 w-96 rounded-full bg-sky-400/10 blur-3xl dark:bg-sky-500/10" />
            <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-violet-500/10 blur-3xl dark:bg-violet-500/10" />
        </div>

        <div class="relative">
            <nav class="sticky top-0 z-40 border-b border-gray-200/80 dark:border-gray-800/80 bg-white/90 dark:bg-gray-900/90 backdrop-blur">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
                                <i class="pi pi-building text-lg" />
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-gray-900 dark:text-gray-100">
                                    {{ trans('site.name') }}
                                </p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                    {{ trans('welcome.nav_subtitle') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                            <Button
                                :icon="isDark ? 'pi pi-sun' : 'pi pi-moon'"
                                text
                                rounded
                                :aria-label="trans('theme.toggle')"
                                @click="toggleTheme"
                                class="!text-gray-600 dark:!text-gray-400"
                            />
                            <Button
                                icon="pi pi-language"
                                text
                                rounded
                                :aria-label="trans('language.label')"
                                @click="(e) => langMenuRef.toggle(e)"
                                class="!text-gray-600 dark:!text-gray-400"
                            />
                            <Menu ref="langMenuRef" :model="langMenuItems" :popup="true" />

                            <template v-if="canLogin">
                                <Link
                                    v-if="page.props.auth.user"
                                    :href="route('dashboard')"
                                    class="hidden sm:inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                                >
                                    {{ trans('common.dashboard') }}
                                </Link>
                                <template v-else>
                                    <Link
                                        :href="route('login')"
                                        class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                                    >
                                        {{ trans('auth.login') }}
                                    </Link>
                                    <Link
                                        v-if="canRegister"
                                        :href="route('register')"
                                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                                    >
                                        {{ trans('auth.register') }}
                                    </Link>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
                <section class="grid gap-10 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] lg:items-center">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700 dark:border-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                            <i class="pi pi-sparkles text-xs" />
                            {{ trans('welcome.badge') }}
                        </div>

                        <h1 class="mt-6 text-4xl sm:text-5xl font-bold tracking-tight text-gray-900 dark:text-white">
                            {{ trans('welcome.hero_title') }}
                        </h1>

                        <p class="mt-5 text-lg leading-8 text-gray-600 dark:text-gray-300">
                            {{ trans('welcome.hero_description') }}
                        </p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <Link
                                v-if="page.props.auth.user"
                                :href="route('dashboard')"
                                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                            >
                                <i class="pi pi-chart-bar" />
                                {{ trans('common.dashboard') }}
                            </Link>

                            <template v-else>
                                <Link
                                    v-if="canLogin"
                                    :href="route('login')"
                                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                                >
                                    <i class="pi pi-sign-in" />
                                    {{ trans('welcome.primary_cta') }}
                                </Link>

                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                                >
                                    <i class="pi pi-user-plus" />
                                    {{ trans('welcome.secondary_cta') }}
                                </Link>
                            </template>
                        </div>

                        <div class="mt-10 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">3</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ trans('welcome.stats.languages') }}</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">100%</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ trans('welcome.stats.responsive') }}</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">1</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ trans('welcome.stats.platform') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4">
                        <div class="rounded-3xl border border-indigo-100 bg-white/90 p-6 shadow-xl shadow-indigo-100/30 dark:border-indigo-900/50 dark:bg-gray-900/90 dark:shadow-none">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                        {{ trans('welcome.preview_label') }}
                                    </p>
                                    <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ trans('welcome.preview_title') }}
                                    </h2>
                                    <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                        {{ trans('welcome.preview_description') }}
                                    </p>
                                </div>
                                <div class="hidden sm:flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
                                    <i class="pi pi-building-columns text-xl" />
                                </div>
                            </div>

                            <div class="mt-6 grid gap-4">
                                <div
                                    v-for="card in heroCards"
                                    :key="card.title"
                                    class="rounded-2xl border border-gray-200/80 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950"
                                >
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
                                            <i :class="card.icon" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                                                {{ card.title }}
                                            </h3>
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                                {{ card.description }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-16">
                    <div class="max-w-2xl">
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                            {{ trans('welcome.benefits_title') }}
                        </h2>
                        <p class="mt-3 text-gray-600 dark:text-gray-300">
                            {{ trans('welcome.benefits_description') }}
                        </p>
                    </div>

                    <div class="mt-8 grid gap-4 md:grid-cols-3">
                        <div
                            v-for="benefit in benefits"
                            :key="benefit.title"
                            class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
                        >
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
                                <i :class="benefit.icon" />
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                                {{ benefit.title }}
                            </h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                {{ benefit.description }}
                            </p>
                        </div>
                    </div>
                </section>

                <footer class="mt-16 border-t border-gray-200 pt-6 text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    {{ trans('site.name') }} · Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}
                </footer>
            </main>
        </div>
    </div>
</template>
