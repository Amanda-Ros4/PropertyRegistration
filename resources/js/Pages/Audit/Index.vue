<script setup>
import { computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import AuditFilters from '@/Components/AuditFilters.vue';
import EmptyState from '@/Components/EmptyState.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import Paginator from 'primevue/paginator';

const props = defineProps({
    logs: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    filterOptions: { type: Object, default: () => ({}) },
});

const page = usePage();

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

function eventLabel(event) {
    return trans(`audit.events.${event}`);
}

function tableLabel(labelKey) {
    return trans(labelKey);
}

function formatDateTime(value) {
    if (!value) {
        return '—';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleString(page.props.locale || 'pt-BR');
}

function onPageChange(event) {
    router.get(route('audit.index'), {
        ...Object.fromEntries(
            Object.entries(props.filters).filter(([, value]) => value !== null && value !== ''),
        ),
        page: event.page + 1,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
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
                v-if="logs.data.length === 0"
                icon="pi pi-history"
                :title="hasActiveFilters ? trans('audit.empty_filtered') : trans('audit.empty')"
                :description="hasActiveFilters ? trans('audit.empty_filtered_description') : trans('audit.empty_description')"
            />

            <template v-else>
                <DataTable
                    :value="logs.data"
                    :rowHover="true"
                    class="rounded-xl overflow-hidden"
                    tableClass="w-full"
                    stripedRows
                >
                    <Column :header="trans('audit.fields.id')" style="width: 80px">
                        <template #body="{ data }">
                            <span class="font-mono font-medium text-indigo-600 dark:text-indigo-400">#{{ data.id }}</span>
                        </template>
                    </Column>
                    <Column :header="trans('audit.fields.user')">
                        <template #body="{ data }">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ data.user_name }}</span>
                                <span class="text-xs text-gray-400">{{ data.user_email || '—' }}</span>
                            </div>
                        </template>
                    </Column>
                    <Column :header="trans('audit.fields.event')" style="width: 140px">
                        <template #body="{ data }">
                            <Tag
                                :value="eventLabel(data.event)"
                                :severity="eventSeverity[data.event] || 'secondary'"
                            />
                        </template>
                    </Column>
                    <Column :header="trans('audit.fields.datetime')" style="width: 180px">
                        <template #body="{ data }">
                            <span class="font-mono text-sm">{{ formatDateTime(data.created_at) }}</span>
                        </template>
                    </Column>
                    <Column :header="trans('audit.fields.table')">
                        <template #body="{ data }">
                            {{ tableLabel(data.table_label_key) }}
                        </template>
                    </Column>
                    <Column :header="trans('audit.fields.audited_id')" style="width: 110px">
                        <template #body="{ data }">
                            <span class="font-mono">{{ data.auditable_id ?? '—' }}</span>
                        </template>
                    </Column>
                    <Column :header="trans('audit.fields.details')" style="width: 120px">
                        <template #body="{ data }">
                            <Button
                                :label="trans('audit.details')"
                                icon="pi pi-eye"
                                text
                                size="small"
                                @click="router.visit(route('audit.show', data.id))"
                            />
                        </template>
                    </Column>
                </DataTable>

                <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ trans('common.showing') }} {{ logs.from }} {{ trans('common.to') }} {{ logs.to }} {{ trans('common.of') }} {{ logs.total }} {{ trans('common.records') }}
                    </span>
                    <Paginator
                        :rows="logs.per_page"
                        :totalRecords="logs.total"
                        :first="(logs.current_page - 1) * logs.per_page"
                        template="PrevPageLink PageLinks NextPageLink"
                        class="border-none p-0"
                        @page="onPageChange"
                    />
                </div>
            </template>
        </div>
    </AppLayout>
</template>
