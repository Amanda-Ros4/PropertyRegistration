<script setup>
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import LocaleThemeControls from '@/Components/LocaleThemeControls.vue';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-16 sm:pt-0 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-200 relative">
        <div class="absolute top-4 right-4 sm:top-6 sm:right-6 z-10">
            <LocaleThemeControls />
        </div>

        <div class="flex flex-col items-center gap-4">
            <Link
                v-if="route().current() !== 'welcome'"
                :href="route('welcome')"
                class="text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 transition-colors"
            >
                ← {{ trans('common.back') }}
            </Link>
            <slot name="logo" />
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-md overflow-hidden sm:rounded-lg border border-slate-200 dark:border-slate-800">
            <slot />
        </div>
    </div>
</template>
