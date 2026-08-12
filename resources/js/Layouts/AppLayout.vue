<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { usePrimeVue } from 'primevue/config';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import { useToast } from 'primevue/usetoast';
import AppBrandMark from '@/Components/AppBrandMark.vue';
import { useTheme } from '@/composables/useTheme';
import { useLocale, SUPPORTED_LOCALES, primeVueLocales } from '@/composables/useLocale';

defineProps({ title: String });

const page = usePage();
const toast = useToast();
const primeVue = usePrimeVue();
const { isDark, toggleTheme } = useTheme();
const { currentLocale, setLocale } = useLocale();

const sidebarOpen = ref(true);
const userMenuRef = ref(null);
const langMenuRef = ref(null);
const lastFlashId = ref(null);

// ─── Flash messages ──────────────────────────────────────────────────────────

function showFlashToast(flash) {
    if (!flash?.message) {
        return;
    }

    const flashId = flash.id ?? `${flash.type}:${flash.message}`;
    if (flashId === lastFlashId.value) {
        return;
    }
    lastFlashId.value = flashId;

    if (flash.type === 'error') {
        toast.add({
            severity: 'error',
            summary: trans('toast.error_summary'),
            detail: flash.message,
            life: 6500,
            closable: true,
        });
        return;
    }

    if (flash.type === 'warn' || flash.type === 'warning') {
        toast.add({
            severity: 'warn',
            summary: trans('toast.warn_summary'),
            detail: flash.message,
            life: 5500,
            closable: true,
        });
        return;
    }

    if (flash.type === 'info') {
        toast.add({
            severity: 'info',
            summary: trans('toast.info_summary'),
            detail: flash.message,
            life: 4500,
            closable: true,
        });
        return;
    }

    toast.add({
        severity: 'success',
        summary: trans('toast.success_summary'),
        detail: flash.message,
        life: 4500,
        closable: true,
    });
}

let removeInertiaSuccessListener = null;

onMounted(() => {
    showFlashToast(page.props.flash);

    removeInertiaSuccessListener = router.on('success', (event) => {
        showFlashToast(event.detail.page.props.flash);
    });
});

onUnmounted(() => {
    removeInertiaSuccessListener?.();
});

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
    router.reload({
        preserveState: true,
        preserveScroll: true,
        headers: {
            'X-Locale': locale,
        },
    });
}

// ─── Navigation ──────────────────────────────────────────────────────────────

const navLinks = [
    { label: () => trans('nav.home'), route: 'dashboard', icon: 'pi pi-home' },
    { label: () => trans('nav.people'), route: 'people.index', icon: 'pi pi-users' },
    { label: () => trans('nav.properties'), route: 'properties.index', icon: 'pi pi-building' },
    { label: () => trans('nav.users'), route: 'users.index', icon: 'pi pi-user' },
    { label: () => trans('nav.settings'), route: 'profile.show', icon: 'pi pi-cog' },
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
        if (routeName === 'people.index') {
            return route().current('people.*');
        }
        if (routeName === 'properties.index') {
            return route().current('properties.*');
        }
        if (routeName === 'users.index') {
            return route().current('users.*');
        }
        if (routeName === 'profile.show') {
            return route().current('profile.*');
        }
        return route().current(routeName);
    } catch {
        return false;
    }
}
</script>

<template>
    <div class="min-h-screen flex bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
        <Head :title="title" />

        <Toast position="top-right" class="app-toast" :pt="{ root: { class: 'app-toast-root' } }" :baseZIndex="1200" />
        <ConfirmDialog />

        <aside
            v-if="sidebarOpen"
            class="w-64 min-h-screen sticky top-0 shrink-0 flex flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800"
        >
            <div class="h-14 px-4 flex items-center gap-3 border-b border-slate-200 dark:border-slate-800">
                <Link :href="route('dashboard')" class="flex items-center gap-3 min-w-0">
                    <AppBrandMark class="h-9 w-9 shrink-0" />
                    <div class="min-w-0 flex flex-col">
                        <span class="truncate font-bold leading-tight text-sm text-slate-800 dark:text-slate-100">
                            {{ trans('site.name') }}
                        </span>
                        <span class="truncate text-xs font-medium text-slate-500 dark:text-slate-400">
                            {{ trans('site.context_short') }}
                        </span>
                    </div>
                </Link>
            </div>

            <nav class="flex-1 p-3 space-y-1">
                <Link
                    v-for="link in navLinks"
                    :key="link.route"
                    :href="route(link.route)"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                        isActiveRoute(link.route)
                            ? 'bg-green-700 text-green-50 dark:bg-green-800 dark:text-green-100'
                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
                    ]"
                >
                    <i :class="link.icon" class="w-5 text-center" />
                    {{ link.label() }}
                </Link>
            </nav>
        </aside>

        <div class="flex-1 min-w-0 flex flex-col">
            <header class="h-14 shrink-0 flex items-center justify-between px-4 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
                <Button
                    :icon="sidebarOpen ? 'pi pi-times' : 'pi pi-bars'"
                    text
                    rounded
                    :aria-label="trans('nav.toggle_sidebar')"
                    @click="sidebarOpen = !sidebarOpen"
                    class="!text-slate-600 dark:!text-slate-400"
                />

                <div class="flex items-center gap-1">
                    <Button
                        :icon="isDark ? 'pi pi-sun' : 'pi pi-moon'"
                        text
                        rounded
                        :aria-label="trans('theme.toggle')"
                        @click="toggleTheme"
                        class="!text-slate-600 dark:!text-slate-400"
                    />
                    <Button
                        icon="pi pi-language"
                        text
                        rounded
                        :aria-label="trans('language.label')"
                        @click="(e) => langMenuRef.toggle(e)"
                        class="!text-slate-600 dark:!text-slate-400"
                    />
                    <Menu ref="langMenuRef" :model="langMenuItems" :popup="true" />
                    <Button
                        :label="$page.props.auth.user.name"
                        icon="pi pi-chevron-down"
                        iconPos="right"
                        text
                        size="small"
                        @click="(e) => userMenuRef.toggle(e)"
                        class="!text-slate-700 dark:!text-slate-300 whitespace-nowrap"
                    />
                    <Menu ref="userMenuRef" :model="userMenuItems" :popup="true" />
                </div>
            </header>

            <main class="flex-1 overflow-auto px-4 sm:px-6 lg:px-8 py-8">
                <slot />
            </main>
        </div>
    </div>
</template>
