<script setup>
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-slate-950">
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

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-slate-900 shadow-md overflow-hidden sm:rounded-lg border border-slate-200 dark:border-slate-800">
            <slot />
        </div>
    </div>
</template>
