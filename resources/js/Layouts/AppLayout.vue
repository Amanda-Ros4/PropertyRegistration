<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import {
    Building2,
    ChevronDown,
    History,
    Home,
    Languages,
    LogOut,
    Menu,
    Moon,
    Sun,
    User,
    Users,
    X,
    Settings,
} from '@lucide/vue';
import { Button } from '@/Components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Toaster } from '@/Components/ui/sonner';
import AppBrandMark from '@/Components/AppBrandMark.vue';
import { useTheme } from '@/composables/useTheme';
import { useLocale, SUPPORTED_LOCALES } from '@/composables/useLocale';
import { useAppToast } from '@/composables/useAppToast';

defineProps({ title: String });

const page = usePage();
const { isDark, toggleTheme } = useTheme();
const { currentLocale, setLocale } = useLocale();

const sidebarOpen = ref(true);
const lastFlashId = ref(null);
const { showFlashToast } = useAppToast();

let removeInertiaSuccessListener = null;

onMounted(() => {
    showFlashToast(page.props.flash, lastFlashId);

    removeInertiaSuccessListener = router.on('success', (event) => {
        showFlashToast(event.detail.page.props.flash, lastFlashId);
    });
});

onUnmounted(() => {
    removeInertiaSuccessListener?.();
});

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

const navLinks = computed(() => {
    const links = [
        { label: () => trans('nav.home'), route: 'dashboard', icon: Home },
        { label: () => trans('nav.people'), route: 'people.index', icon: Users },
        { label: () => trans('nav.properties'), route: 'properties.index', icon: Building2 },
    ];

    if (page.props.permissions?.canManageUsers) {
        links.push({ label: () => trans('nav.users'), route: 'users.index', icon: User });
    }

    if (page.props.permissions?.canViewAudit) {
        links.push({ label: () => trans('nav.audit'), route: 'audit.index', icon: History });
    }

    links.push({ label: () => trans('nav.settings'), route: 'profile.show', icon: Settings });

    return links;
});

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
        if (routeName === 'audit.index') {
            return route().current('audit.*');
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
        <Toaster class="pointer-events-auto" position="top-right" rich-colors
            :theme="isDark ? 'dark' : 'light'" />

        <aside v-if="sidebarOpen"
            class="w-64 min-h-screen sticky top-0 shrink-0 flex flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800">
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
                <Link v-for="link in navLinks" :key="link.route" :href="route(link.route)" :class="[
                    'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                    isActiveRoute(link.route)
                        ? 'bg-green-700 text-green-50 dark:bg-green-800 dark:text-green-100'
                        : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800',
                ]">
                    <component :is="link.icon" class="size-5 shrink-0" />
                    {{ link.label() }}
                </Link>
            </nav>
        </aside>

        <div class="flex-1 min-w-0 flex flex-col">
            <header
                class="h-14 shrink-0 flex items-center justify-between px-4 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
                <Button variant="ghost" size="icon" :aria-label="trans('nav.toggle_sidebar')"
                    @click="sidebarOpen = !sidebarOpen">
                    <X v-if="sidebarOpen" class="size-5" />
                    <Menu v-else class="size-5" />
                </Button>

                <div class="flex items-center gap-1">
                    <Button variant="ghost" size="icon" :aria-label="trans('theme.toggle')" @click="toggleTheme">
                        <Sun v-if="isDark" class="size-5" />
                        <Moon v-else class="size-5" />
                    </Button>

                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="icon" :aria-label="trans('language.label')">
                                <Languages class="size-5" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem v-for="locale in SUPPORTED_LOCALES" :key="locale.code"
                                :class="locale.code === currentLocale ? 'font-semibold' : ''"
                                @click="changeLocale(locale.code)">
                                {{ locale.label }}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm" class="whitespace-nowrap">
                                {{ $page.props.auth.user.name }}
                                <ChevronDown class="size-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem @click="router.visit(route('profile.show'))">
                                <User class="size-4" />
                                {{ trans('common.profile') }}
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem @click="router.post(route('logout'))">
                                <LogOut class="size-4" />
                                {{ trans('common.logout') }}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </header>

            <main class="flex-1 overflow-auto px-4 sm:px-6 lg:px-8 py-8">
                <slot />
            </main>
        </div>
    </div>
</template>
