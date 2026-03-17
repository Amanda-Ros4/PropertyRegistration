<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import AppBrandMark from '@/Components/AppBrandMark.vue';
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
        iconClass: 'text-green-700 dark:text-green-400',
        bgClass: 'bg-green-50 dark:bg-green-950/20',
        title: () => trans('welcome.people_title'),
        description: () => trans('welcome.people_description'),
    },
    {
        icon: 'pi pi-building',
        iconClass: 'text-green-800 dark:text-green-300',
        bgClass: 'bg-green-100 dark:bg-green-950/30',
        title: () => trans('welcome.properties_title'),
        description: () => trans('welcome.properties_description'),
    },
    {
        icon: 'pi pi-shield',
        iconClass: 'text-emerald-600 dark:text-emerald-400',
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

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-200">
        <div class="relative">
            <div class="border-b border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-900/80">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                    {{ trans('welcome.agency') }}
                </div>
            </div>

            <nav class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex min-h-14 items-center justify-between gap-4 py-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <AppBrandMark class="h-10 w-10 shrink-0" />
                                <div class="flex min-w-0 flex-col">
                                    <span class="truncate font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ trans('site.name') }}</span>
                                    <span class="truncate text-xs font-medium text-slate-500 dark:text-slate-400">{{ trans('site.context_short') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Button
                                icon="pi pi-language"
                                text
                                rounded
                                :label="currentLocaleLabel"
                                class="!text-slate-600 dark:!text-slate-400"
                                :aria-label="trans('language.label')"
                                @click="(e) => langMenuRef.toggle(e)"
                            />
                            <Menu ref="langMenuRef" :model="langMenuItems" :popup="true" />

                            <Button
                                :icon="isDark ? 'pi pi-sun' : 'pi pi-moon'"
                                text
                                rounded
                                :aria-label="trans('theme.toggle')"
                                class="!text-slate-600 dark:!text-slate-400"
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
                <section class="grid grid-cols-1 lg:grid-cols-[1.15fr_0.85fr] gap-6 items-stretch">
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 sm:p-10 shadow-sm">
                        <div class="max-w-3xl">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-green-700 dark:text-green-400 mb-4">
                                {{ trans('welcome.badge') }}
                            </p>
                            <h1 class="text-3xl sm:text-4xl font-bold leading-tight mb-4 text-slate-900 dark:text-slate-100">
                                {{ trans('welcome.hero_title') }}
                            </h1>
                            <p class="text-slate-600 dark:text-slate-300 text-base sm:text-lg leading-relaxed mb-8">
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
                                    />
                                </Link>

                                <template v-else>
                                    <Link v-if="canLogin" :href="route('login')">
                                        <Button
                                            :label="trans('auth.login')"
                                            icon="pi pi-sign-in"
                                            severity="secondary"
                                        />
                                    </Link>
                                    <Link v-if="canRegister" :href="route('register')">
                                        <Button
                                            :label="trans('auth.register')"
                                            icon="pi pi-user-plus"
                                        />
                                    </Link>
                                </template>
                            </div>

                            <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-4">
                                    <p class="text-xs uppercase tracking-wide font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ trans('dashboard.total_people') }}</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-200">{{ trans('welcome.people_summary') }}</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-4">
                                    <p class="text-xs uppercase tracking-wide font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ trans('dashboard.total_properties') }}</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-200">{{ trans('welcome.properties_summary') }}</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-4">
                                    <p class="text-xs uppercase tracking-wide font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ trans('welcome.workflow_title') }}</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-200">{{ trans('welcome.workflow_summary') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm p-6 sm:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-950/20 flex items-center justify-center">
                                <i class="pi pi-chart-bar text-green-700 dark:text-green-400 text-xl" />
                            </div>
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('welcome.quick_overview') }}</p>
                                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ trans('welcome.panel_title') }}</h2>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 p-4">
                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100 mb-1">{{ trans('welcome.step_one_title') }}</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('welcome.step_one_description') }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 p-4">
                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100 mb-1">{{ trans('welcome.step_two_title') }}</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('welcome.step_two_description') }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 p-4">
                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100 mb-1">{{ trans('welcome.step_three_title') }}</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('welcome.step_three_description') }}</p>
                            </div>
                            <div class="rounded-xl border border-green-200 dark:border-green-900/40 bg-green-50 dark:bg-green-950/20 p-4">
                                <p class="text-sm font-semibold text-green-800 dark:text-green-300 mb-1">{{ trans('welcome.audience_title') }}</p>
                                <p class="text-sm text-green-800/90 dark:text-green-200">{{ trans('welcome.audience_description') }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="card in featureCards"
                        :key="card.icon"
                        class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm"
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
