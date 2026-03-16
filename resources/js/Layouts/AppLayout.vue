<script setup>
import { ref, watch, onMounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { usePrimeVue } from 'primevue/config';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import { useToast } from 'primevue/usetoast';
import { useTheme } from '@/composables/useTheme';
import { useLocale, SUPPORTED_LOCALES, primeVueLocales } from '@/composables/useLocale';

defineProps({ title: String });

const page = usePage();
const toast = useToast();
const primeVue = usePrimeVue();
const { isDark, toggleTheme } = useTheme();
const { currentLocale, setLocale } = useLocale();

const mobileMenuOpen = ref(false);
const userMenuRef = ref(null);
const langMenuRef = ref(null);

// ─── Flash messages ──────────────────────────────────────────────────────────

watch(() => page.props.flash, (flash) => {
    if (flash?.message) {
        toast.add({
            severity: flash.type === 'error' ? 'error' : 'success',
            summary: flash.type === 'error' ? trans('errors.server') : '',
            detail: flash.message,
            life: 4000,
        });
    }
}, { immediate: true, deep: true });

// ─── Locale sync ─────────────────────────────────────────────────────────────

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
}

// ─── Navigation ──────────────────────────────────────────────────────────────

const navLinks = [
    { label: () => trans('nav.dashboard'), route: 'dashboard', icon: 'pi pi-home' },
    { label: () => trans('nav.people'), route: 'people.index', icon: 'pi pi-users' },
    { label: () => trans('nav.properties'), route: 'properties.index', icon: 'pi pi-building' },
];

const userMenuItems = ref([
    {
        label: () => trans('common.profile'),
        icon: 'pi pi-user',
        command: () => router.visit(route('profile.show')),
    },
    { separator: true },
    {
        label: () => trans('common.logout'),
        icon: 'pi pi-sign-out',
        command: () => router.post(route('logout')),
    },
]);

const langMenuItems = ref(
    SUPPORTED_LOCALES.map(l => ({
        label: l.label,
        command: () => changeLocale(l.code),
    }))
);

function isActiveRoute(routeName) {
    try {
        return route().current(routeName) || route().current(routeName + '.*');
    } catch {
        return false;
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 transition-colors duration-200">
        <Head :title="title" />

        <Toast position="top-right" />
        <ConfirmDialog />

        <!-- ─── Top Navigation ──────────────────────────────────────────────── -->
        <nav class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-14 min-h-14">

                    <!-- Logo + Nav Links (desktop) -->
                    <div class="flex items-center gap-6 min-w-0">
                        <Link :href="route('dashboard')" class="flex items-center gap-2 font-bold text-indigo-600 dark:text-indigo-400 text-lg shrink-0">
                            <i class="pi pi-building text-xl" />
                            <span class="hidden sm:inline truncate">PropertyReg</span>
                        </Link>

                        <div class="hidden lg:flex items-center gap-1">
                            <Link
                                v-for="link in navLinks"
                                :key="link.route"
                                :href="route(link.route)"
                                :class="[
                                    'flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap',
                                    isActiveRoute(link.route)
                                        ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300'
                                        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
                                ]"
                            >
                                <i :class="link.icon" />
                                {{ link.label() }}
                            </Link>
                        </div>
                    </div>

                    <!-- Desktop: theme, language, user -->
                    <div class="hidden lg:flex items-center gap-1 shrink-0">
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
                        <Button
                            :label="$page.props.auth.user.name"
                            icon="pi pi-chevron-down"
                            iconPos="right"
                            text
                            size="small"
                            @click="(e) => userMenuRef.toggle(e)"
                            class="!text-gray-700 dark:!text-gray-300 max-w-[180px]"
                        />
                        <Menu ref="userMenuRef" :model="userMenuItems" :popup="true" />
                    </div>

                    <!-- Mobile: only hamburger -->
                    <Button
                        :icon="mobileMenuOpen ? 'pi pi-times' : 'pi pi-bars'"
                        text
                        rounded
                        class="lg:hidden !text-gray-600 dark:!text-gray-400 shrink-0"
                        :aria-label="mobileMenuOpen ? 'Close menu' : 'Open menu'"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    />
                </div>
            </div>

            <!-- Mobile menu (hamburger content) -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-show="mobileMenuOpen" class="lg:hidden border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden">
                    <div class="px-4 py-4 space-y-1">
                        <!-- Nav links -->
                        <Link
                            v-for="link in navLinks"
                            :key="link.route"
                            :href="route(link.route)"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                                isActiveRoute(link.route)
                                    ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300'
                                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
                            ]"
                            @click="mobileMenuOpen = false"
                        >
                            <i :class="link.icon" class="w-5 text-center" />
                            {{ link.label() }}
                        </Link>

                        <div class="border-t border-gray-100 dark:border-gray-800 my-3" />

                        <!-- Theme toggle -->
                        <button
                            type="button"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium w-full text-left text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                            @click="toggleTheme"
                        >
                            <i :class="isDark ? 'pi pi-sun' : 'pi pi-moon'" class="w-5 text-center" />
                            {{ isDark ? trans('theme.light') : trans('theme.dark') }}
                        </button>

                        <!-- Language -->
                        <div class="px-3 py-2">
                            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-2">{{ trans('language.label') }}</p>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="loc in SUPPORTED_LOCALES"
                                    :key="loc.code"
                                    type="button"
                                    :class="[
                                        'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
                                        currentLocale === loc.code
                                            ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300'
                                            : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
                                    ]"
                                    @click="changeLocale(loc.code); mobileMenuOpen = false"
                                >
                                    {{ loc.label }}
                                </button>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 dark:border-gray-800 my-3" />

                        <!-- User section -->
                        <div class="px-3 py-2">
                            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-2">{{ $page.props.auth.user.name }}</p>
                            <div class="space-y-1">
                                <Link
                                    :href="route('profile.show')"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                    @click="mobileMenuOpen = false"
                                >
                                    <i class="pi pi-user w-5 text-center" />
                                    {{ trans('common.profile') }}
                                </Link>
                                <button
                                    type="button"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium w-full text-left text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                    @click="router.post(route('logout')); mobileMenuOpen = false"
                                >
                                    <i class="pi pi-sign-out w-5 text-center" />
                                    {{ trans('common.logout') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </nav>

        <!-- ─── Page Content ───────────────────────────────────────────────── -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>
    </div>
</template>
