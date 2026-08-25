<script setup>
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import LocaleThemeControls from '@/Components/LocaleThemeControls.vue';
import { AUTH_BACK_LINK_CLASS } from '@/lib/auth-ui';
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center py-16 sm:py-10 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-200 relative">
        <div class="absolute top-4 right-4 sm:top-6 sm:right-6 z-10">
            <LocaleThemeControls />
        </div>

        <div class="flex flex-col items-center gap-4">
            <Link
                v-if="route().current() !== 'welcome'"
                :href="route('welcome')"
                class="text-sm"
                :class="AUTH_BACK_LINK_CLASS"
            >
                ← {{ trans('common.back') }}
            </Link>
            <slot name="logo" />
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-md sm:rounded-lg border border-slate-200 dark:border-slate-800">
            <slot />
        </div>
    </div>
</template>
