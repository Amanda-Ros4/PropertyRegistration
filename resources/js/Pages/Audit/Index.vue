<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import FilterBar from '@/Components/FilterBar.vue';
import EmptyState from '@/Components/EmptyState.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Paginator from 'primevue/paginator';

const props = defineProps({
    logs: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();

const actionSeverity = {
    created: 'success',
    updated: 'info',
    activated: 'success',
    deactivated: 'warn',
    endorsed: 'info',
};

function actionLabel(action) {
    return trans(`audit.actions.${action}`);
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
        ...props.filters,
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

        <FilterBar
            routeName="audit.index"
            :searchPlaceholder="trans('audit.search_placeholder')"
            :initialSearch="filters.search"
        />

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <EmptyState
                v-if="logs.data.length === 0"
                icon="pi pi-history"
                :title="trans('audit.empty')"
                :description="trans('audit.empty_description')"
            />

            <template v-else>
                <DataTable
                    :value="logs.data"
                    :rowHover="true"
                    class="rounded-xl overflow-hidden"
                    tableClass="w-full"
                    stripedRows
                >
                    <Column :header="trans('audit.fields.date')" style="width: 180px">
                        <template #body="{ data }">
                            <span class="font-mono text-sm">{{ formatDateTime(data.created_at) }}</span>
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
                    <Column :header="trans('audit.fields.action')" style="width: 140px">
                        <template #body="{ data }">
                            <Tag
                                :value="actionLabel(data.action)"
                                :severity="actionSeverity[data.action] || 'secondary'"
                            />
                        </template>
                    </Column>
                    <Column field="description" :header="trans('audit.fields.description')" />
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
