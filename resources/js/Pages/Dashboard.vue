<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';

const props = defineProps({
    stats: { type: Object, default: () => ({ totalPeople: 0, totalProperties: 0 }) },
    recentPeople: { type: Array, default: () => [] },
    recentProperties: { type: Array, default: () => [] },
});

function formatCpf(cpf) {
    const digits = cpf?.replace(/\D/g, '') ?? '';
    if (digits.length === 11) {
        return `${digits.slice(0,3)}.${digits.slice(3,6)}.${digits.slice(6,9)}-${digits.slice(9)}`;
    }
    return cpf;
}

const genderLabel = {
    male: () => trans('genders.male'),
    female: () => trans('genders.female'),
    other: () => trans('genders.other'),
    prefer_not_to_say: () => trans('genders.prefer_not_to_say'),
};
</script>

<template>
    <AppLayout :title="trans('dashboard.title')">
        <Head :title="trans('dashboard.title')" />

        <!-- Welcome Banner -->
        <div class="mb-8 p-6 bg-gradient-to-r from-indigo-600 to-indigo-700 dark:from-indigo-800 dark:to-indigo-900 rounded-xl text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1">{{ trans('dashboard.welcome') }}</h1>
                    <p class="text-indigo-200 text-sm">{{ trans('dashboard.description') }}</p>
                </div>
                <i class="pi pi-building text-5xl text-indigo-300 hidden sm:block" />
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
            <Link :href="route('people.index')" class="block">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors cursor-pointer group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('dashboard.total_people') }}</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ stats.totalPeople }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/40 flex items-center justify-center group-hover:bg-blue-100 dark:group-hover:bg-blue-900/40 transition-colors">
                            <i class="pi pi-users text-xl text-blue-500" />
                        </div>
                    </div>
                </div>
            </Link>

            <Link :href="route('properties.index')" class="block">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors cursor-pointer group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('dashboard.total_properties') }}</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ stats.totalProperties }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/40 transition-colors">
                            <i class="pi pi-building text-xl text-indigo-500" />
                        </div>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Recent Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent People -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800">
                    <h2 class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <i class="pi pi-users text-blue-500" />
                        {{ trans('dashboard.recent_people') }}
                    </h2>
                    <Link :href="route('people.index')">
                        <Button :label="trans('dashboard.view_all')" text size="small" />
                    </Link>
                </div>
                <div v-if="recentPeople.length === 0" class="p-8 text-center text-gray-400 dark:text-gray-500 text-sm">
                    {{ trans('dashboard.no_people') }}
                </div>
                <ul v-else class="divide-y divide-gray-50 dark:divide-gray-800">
                    <li
                        v-for="person in recentPeople"
                        :key="person.id"
                        class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                    >
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ person.name }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ formatCpf(person.cpf) }}</p>
                        </div>
                        <Link :href="route('people.edit', person.id)">
                            <Button icon="pi pi-pencil" text rounded size="small" severity="secondary" />
                        </Link>
                    </li>
                </ul>
            </div>

            <!-- Recent Properties -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800">
                    <h2 class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <i class="pi pi-building text-indigo-500" />
                        {{ trans('dashboard.recent_properties') }}
                    </h2>
                    <Link :href="route('properties.index')">
                        <Button :label="trans('dashboard.view_all')" text size="small" />
                    </Link>
                </div>
                <div v-if="recentProperties.length === 0" class="p-8 text-center text-gray-400 dark:text-gray-500 text-sm">
                    {{ trans('dashboard.no_properties') }}
                </div>
                <ul v-else class="divide-y divide-gray-50 dark:divide-gray-800">
                    <li
                        v-for="property in recentProperties"
                        :key="property.id"
                        class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                    >
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ property.street }}, {{ property.number }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ property.neighborhood }}
                                <span v-if="property.person"> · {{ property.person.name }}</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono text-indigo-500 dark:text-indigo-400">#{{ property.id }}</span>
                            <Link :href="route('properties.edit', property.id)">
                                <Button icon="pi pi-pencil" text rounded size="small" severity="secondary" />
                            </Link>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
