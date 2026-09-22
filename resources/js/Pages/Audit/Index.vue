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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';

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

        <div class="max-w-7xl mx-auto space-y-6">

            <PageHeader :title="trans('audit.title')" :subtitle="trans('audit.subtitle')" />

            <div
                class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                <AuditFilters :filters="filters" :filterOptions="filterOptions" />
            </div>

            <div
                class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6">
                <EmptyState v-if="rows.length === 0" icon="history"
                    :title="hasActiveFilters ? trans('audit.empty_filtered') : trans('audit.empty')"
                    :description="hasActiveFilters ? trans('audit.empty_filtered_description') : trans('audit.empty_description')" />

                <template v-else>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-20">{{ trans('audit.fields.id') }}</TableHead>
                                    <TableHead>{{ trans('audit.fields.user') }}</TableHead>
                                    <TableHead class="w-32">{{ trans('audit.fields.event') }}</TableHead>
                                    <TableHead class="w-48">{{ trans('audit.fields.datetime') }}</TableHead>
                                    <TableHead class="w-36">{{ trans('audit.fields.table') }}</TableHead>
                                    <TableHead class="w-32">{{ trans('audit.fields.audited_id') }}</TableHead>
                                    <TableHead class="w-24 text-center">{{ trans('audit.fields.details') }}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow 
                                    v-for="row in rows" 
                                    :key="row.id"
                                    class="border-b-0 even:bg-slate-50 dark:even:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                >
                                    <TableCell class="font-medium text-indigo-600 dark:text-indigo-400">
                                        #{{ row.id }}
                                    </TableCell>
                                    
                                    <TableCell>
                                        <div class="font-medium">{{ row.user_name }}</div>
                                        <div class="text-xs text-slate-400">{{ row.user_email || '—' }}</div>
                                    </TableCell>
                                    
                                    <TableCell>
                                        <StatusBadge 
                                            :value="eventLabel(row.event)"
                                            :severity="eventSeverity[row.event] || 'secondary'" 
                                        />
                                    </TableCell>
                                    
                                    <TableCell class="whitespace-nowrap">
                                        {{ formatDateTime(row.created_at) }}
                                    </TableCell>
                                    
                                    <TableCell>
                                        {{ tableLabel(row.table_label_key) }}
                                    </TableCell>
                                    
                                    <TableCell>
                                        {{ row.auditable_id ?? '—' }}
                                    </TableCell>
                                    
                                    <TableCell>
                                        <div class="flex items-center justify-center">
                                            <TableIconButton 
                                                icon="eye" 
                                                :label="trans('audit.details')"
                                                @click="router.visit(route('audit.show', row.id))" 
                                            />
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-sm text-slate-500 dark:text-slate-400">
                            {{ trans('common.showing') }} {{ logs.from }} {{ trans('common.to') }} {{ logs.to }} {{
                                trans('common.of') }} {{ logs.total }} {{ trans('common.records') }}
                        </span>
                        <InertiaPagination :paginator="logs" route-name="audit.index" :query="paginationQuery" />
                    </div>
                </template>
            </div>

        </div>
    </AppLayout>
</template>