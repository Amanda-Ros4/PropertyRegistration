<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Building2, Users } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TableIconButton from '@/Components/TableIconButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatCpfDisplay } from '@/utils/formatting';

const props = defineProps({
    stats: { type: Object, default: () => ({ totalPeople: 0, totalProperties: 0 }) },
    recentPeople: { type: Array, default: () => [] },
    recentProperties: { type: Array, default: () => [] },
});
</script>

<template>
    <AppLayout :title="trans('dashboard.title')">
        <Head :title="trans('dashboard.title')" />

        <div class="mb-8 grid grid-cols-1 xl:grid-cols-[1.25fr_0.75fr] gap-6">
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700 dark:text-green-400 mb-3">
                    {{ trans('dashboard.context') }}
                </p>
                <h1 class="text-2xl font-bold mb-2 text-slate-900 dark:text-slate-100">{{ trans('dashboard.welcome') }}</h1>
                <p class="text-sm text-slate-600 dark:text-slate-400 max-w-3xl">{{ trans('dashboard.description') }}</p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <PrimaryButton type="button" @click="router.visit(route('people.create'))">
                        {{ trans('people.create') }}
                    </PrimaryButton>
                    <PrimaryButton type="button" @click="router.visit(route('properties.create'))">
                        {{ trans('properties.create') }}
                    </PrimaryButton>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
                <p class="text-sm font-semibold text-green-700 dark:text-green-300 mb-2">{{ trans('dashboard.operational_summary_title') }}</p>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-5">{{ trans('dashboard.operational_summary_description') }}</p>
                <div class="space-y-3">
                    <div class="rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">{{ trans('dashboard.total_people') }}</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.totalPeople }}</p>
                    </div>
                    <div class="rounded-lg bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">{{ trans('dashboard.total_properties') }}</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.totalProperties }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
            <Link :href="route('people.index')" class="block">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm hover:border-green-300 dark:hover:border-green-700 transition-colors cursor-pointer group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ trans('dashboard.total_people') }}</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ stats.totalPeople }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-950/20 flex items-center justify-center group-hover:bg-green-100 dark:group-hover:bg-green-900/30 transition-colors">
                            <Users class="size-5 text-green-700 dark:text-green-400" />
                        </div>
                    </div>
                </div>
            </Link>

            <Link :href="route('properties.index')" class="block">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm hover:border-green-300 dark:hover:border-green-700 transition-colors cursor-pointer group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ trans('dashboard.total_properties') }}</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ stats.totalProperties }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-950/30 flex items-center justify-center group-hover:bg-green-200 dark:group-hover:bg-green-900/30 transition-colors">
                            <Building2 class="size-5 text-green-800 dark:text-green-300" />
                        </div>
                    </div>
                </div>
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <Users class="size-4 text-green-700 dark:text-green-400" />
                        {{ trans('dashboard.recent_people') }}
                    </h2>
                    <SecondaryButton type="button" @click="router.visit(route('people.index'))">
                        {{ trans('dashboard.view_all') }}
                    </SecondaryButton>
                </div>
                <div v-if="recentPeople.length === 0" class="p-8 text-center text-slate-400 dark:text-slate-500 text-sm">
                    {{ trans('dashboard.no_people') }}
                </div>
                <ul v-else class="divide-y divide-slate-50 dark:divide-slate-800">
                    <li
                        v-for="person in recentPeople"
                        :key="person.id"
                        class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                    >
                        <div>
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ person.name }}</p>
                            <p class="text-xs text-slate-400">{{ formatCpfDisplay(person.cpf) }}</p>
                        </div>
                        <TableIconButton
                            icon="eye"
                            :label="trans('common.view')"
                            @click="router.visit(route('people.edit', person.id))"
                        />
                    </li>
                </ul>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <Building2 class="size-4 text-green-800 dark:text-green-300" />
                        {{ trans('dashboard.recent_properties') }}
                    </h2>
                    <SecondaryButton type="button" @click="router.visit(route('properties.index'))">
                        {{ trans('dashboard.view_all') }}
                    </SecondaryButton>
                </div>
                <div v-if="recentProperties.length === 0" class="p-8 text-center text-slate-400 dark:text-slate-500 text-sm">
                    {{ trans('dashboard.no_properties') }}
                </div>
                <ul v-else class="divide-y divide-slate-50 dark:divide-slate-800">
                    <li
                        v-for="property in recentProperties"
                        :key="property.id"
                        class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                    >
                        <div>
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                {{ property.street }}, {{ property.number }}
                            </p>
                            <p class="text-xs text-slate-400">
                                {{ property.neighborhood }}
                                <span v-if="property.person"> · {{ property.person.name }}</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-600 dark:text-slate-300">#{{ property.id }}</span>
                            <TableIconButton
                                icon="eye"
                                :label="trans('common.view')"
                                @click="router.visit(route('properties.edit', property.id))"
                            />
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
