<script setup>
import { computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import TableIconButton from '@/Components/TableIconButton.vue';
import { formatDateTimeDisplay } from '@/utils/formatting';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import AuditFilters from '@/Components/AuditFilters.vue';
import EmptyState from '@/Components/EmptyState.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import InertiaPagination from '@/Components/InertiaPagination.vue';

const props = defineProps({
    logs: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    filterOptions: { type: Object, default: () => ({}) },
});

const page = usePage();

const paginationQuery = computed(() =>
    Object.fromEntries(
        Object.entries(props.filters).filter(([, value]) => value !== null && value !== ''),
    ),
);

const eventSeverity = {
    created: 'success',
    updated: 'info',
    deleted: 'danger',
    restored: 'warn',
};

const hasActiveFilters = computed(() =>
    Boolean(
        props.filters.user_id
        || props.filters.event
        || props.filters.date
        || props.filters.auditable_type,
    ),
);

const rows = computed(() => props.logs.data ?? []);

function eventLabel(event) {
    return event ? trans(`audit.events.${event}`) : '—';
}

function tableLabel(labelKey) {
    return labelKey ? trans(labelKey) : '—';
}

function formatDateTime(value) {
    return formatDateTimeDisplay(value, page.props.locale);
}
</script>

<template>
    <AppLayout :title="trans('audit.title')">
        <Head :title="trans('audit.title')" />

        <PageHeader
            :title="trans('audit.title')"
            :subtitle="trans('audit.subtitle')"
        />

        <AuditFilters
            :filters="filters"
            :filterOptions="filterOptions"
        />

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <EmptyState
                v-if="rows.length === 0"
                icon="history"
                :title="hasActiveFilters ? trans('audit.empty_filtered') : trans('audit.empty')"
                :description="hasActiveFilters ? trans('audit.empty_filtered_description') : trans('audit.empty_description')"
            />

            <template v-else>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300 w-20">
                                    {{ trans('audit.fields.id') }}
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">
                                    {{ trans('audit.fields.user') }}
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300 w-36">
                                    {{ trans('audit.fields.event') }}
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300 w-44">
                                    {{ trans('audit.fields.datetime') }}
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">
                                    {{ trans('audit.fields.table') }}
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300 w-28">
                                    {{ trans('audit.fields.audited_id') }}
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300 w-28">
                                    {{ trans('audit.fields.details') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr
                                v-for="row in rows"
                                :key="row.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                            >
                                <td class="px-4 py-3 font-medium text-indigo-600 dark:text-indigo-400">
                                    #{{ row.id }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ row.user_name }}</div>
                                    <div class="text-xs text-gray-400">{{ row.user_email || '—' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <StatusBadge
                                        :value="eventLabel(row.event)"
                                        :severity="eventSeverity[row.event] || 'secondary'"
                                    />
                                </td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    {{ formatDateTime(row.created_at) }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ tableLabel(row.table_label_key) }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ row.auditable_id ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <TableIconButton
                                        icon="eye"
                                        :label="trans('audit.details')"
                                        @click="router.visit(route('audit.show', row.id))"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ trans('common.showing') }} {{ logs.from }} {{ trans('common.to') }} {{ logs.to }} {{ trans('common.of') }} {{ logs.total }} {{ trans('common.records') }}
                    </span>
                    <InertiaPagination
                        :paginator="logs"
                        route-name="audit.index"
                        :query="paginationQuery"
                    />
                </div>
            </template>
        </div>
    </AppLayout>
</template>
