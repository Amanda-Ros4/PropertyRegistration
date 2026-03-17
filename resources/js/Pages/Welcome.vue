<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import { useTheme } from '@/composables/useTheme';
import { useLocale, SUPPORTED_LOCALES } from '@/composables/useLocale';

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

const { isDark, toggleTheme } = useTheme();
const { currentLocale, setLocale } = useLocale();
const langMenuRef = ref(null);

const featureCards = [
    {
        icon: 'pi pi-users',
        iconClass: 'text-blue-500',
        bgClass: 'bg-blue-50 dark:bg-blue-950/40',
        title: () => trans('welcome.people_title'),
        description: () => trans('welcome.people_description'),
    },
    {
        icon: 'pi pi-building',
        iconClass: 'text-indigo-500',
        bgClass: 'bg-indigo-50 dark:bg-indigo-950/40',
        title: () => trans('welcome.properties_title'),
        description: () => trans('welcome.properties_description'),
    },
    {
        icon: 'pi pi-shield',
        iconClass: 'text-emerald-500',
        bgClass: 'bg-emerald-50 dark:bg-emerald-950/40',
        title: () => trans('welcome.security_title'),
        description: () => trans('welcome.security_description'),
    },
];

const langMenuItems = ref(
    SUPPORTED_LOCALES.map((locale) => ({
        label: locale.label,
        command: () => changeLocale(locale.code),
    }))
);

const currentLocaleLabel = computed(
    () => SUPPORTED_LOCALES.find((locale) => locale.code === currentLocale.value)?.label ?? currentLocale.value
);

async function changeLocale(locale) {
    await setLocale(locale);
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
    <Head :title="trans('welcome.title')" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 transition-colors duration-200">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full bg-indigo-500/15 blur-3xl" />
            <div class="absolute top-1/3 -right-24 h-80 w-80 rounded-full bg-blue-500/10 blur-3xl" />
        </div>

        <div class="relative">
            <nav class="border-b border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-900/90 backdrop-blur">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex min-h-14 items-center justify-between gap-4 py-3">
                        <div class="flex items-center gap-2 font-bold text-indigo-600 dark:text-indigo-400 text-lg">
                            <i class="pi pi-building text-xl" />
                            <span class="truncate">{{ trans('site.name') }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <Button
                                icon="pi pi-language"
                                text
                                rounded
                                :label="currentLocaleLabel"
                                class="!text-gray-600 dark:!text-gray-400"
                                :aria-label="trans('language.label')"
                                @click="(e) => langMenuRef.toggle(e)"
                            />
                            <Menu ref="langMenuRef" :model="langMenuItems" :popup="true" />

                            <Button
                                :icon="isDark ? 'pi pi-sun' : 'pi pi-moon'"
                                text
                                rounded
                                :aria-label="trans('theme.toggle')"
                                class="!text-gray-600 dark:!text-gray-400"
                                @click="toggleTheme"
                            />

                            <template v-if="canLogin">
                                <Link
                                    v-if="$page.props.auth.user"
                                    :href="route('dashboard')"
                                >
                                    <Button :label="trans('nav.dashboard')" size="small" />
                                </Link>

                                <template v-else>
                                    <Link :href="route('login')">
                                        <Button
                                            :label="trans('auth.login')"
                                            size="small"
                                            outlined
                                            severity="secondary"
                                        />
                                    </Link>
                                    <Link v-if="canRegister" :href="route('register')">
                                        <Button :label="trans('auth.register')" size="small" />
                                    </Link>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                <section class="grid grid-cols-1 lg:grid-cols-[1.2fr_0.8fr] gap-6 items-stretch">
                    <div class="rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-700 dark:from-indigo-800 dark:to-indigo-900 text-white p-8 sm:p-10 shadow-lg">
                        <div class="max-w-2xl">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-200 mb-4">
                                {{ trans('welcome.badge') }}
                            </p>
                            <h1 class="text-3xl sm:text-4xl font-bold leading-tight mb-4">
                                {{ trans('welcome.hero_title') }}
                            </h1>
                            <p class="text-indigo-100 text-base sm:text-lg leading-relaxed mb-8">
                                {{ trans('welcome.hero_description') }}
                            </p>

                            <div class="flex flex-wrap gap-3">
                                <Link
                                    v-if="$page.props.auth.user"
                                    :href="route('dashboard')"
                                >
                                    <Button
                                        :label="trans('welcome.primary_cta')"
                                        icon="pi pi-arrow-right"
                                        iconPos="right"
                                        severity="contrast"
                                    />
                                </Link>

                                <template v-else>
                                    <Link v-if="canLogin" :href="route('login')">
                                        <Button
                                            :label="trans('auth.login')"
                                            icon="pi pi-sign-in"
                                        />
                                    </Link>
                                    <Link v-if="canRegister" :href="route('register')">
                                        <Button
                                            :label="trans('welcome.secondary_cta')"
                                            icon="pi pi-user-plus"
                                            outlined
                                            severity="contrast"
                                        />
                                    </Link>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm p-6 sm:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center">
                                <i class="pi pi-chart-bar text-indigo-500 text-xl" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ trans('welcome.quick_overview') }}</p>
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ trans('welcome.panel_title') }}</h2>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 p-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">{{ trans('dashboard.total_people') }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ trans('welcome.people_summary') }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 p-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">{{ trans('dashboard.total_properties') }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ trans('welcome.properties_summary') }}</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 p-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">{{ trans('profile.title') }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ trans('welcome.profile_summary') }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="card in featureCards"
                        :key="card.icon"
                        class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm"
                    >
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4" :class="card.bgClass">
                            <i :class="[card.icon, card.iconClass, 'text-xl']" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            {{ card.title() }}
                        </h3>
                        <p class="text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                            {{ card.description() }}
                        </p>
                    </div>
                </section>

                <footer class="py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ trans('welcome.footer') }} Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}
                </footer>
            </main>
        </div>
    </div>
</template>
