<script setup>
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import FilterBar from '@/Components/FilterBar.vue';
import EmptyState from '@/Components/EmptyState.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Paginator from 'primevue/paginator';
import { formatCpfDisplay, formatDateDisplay, formatPhoneDisplay } from '@/utils/formatting';

const props = defineProps({
    people: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const genderSeverity = {
    male: 'info',
    female: 'success',
    other: 'secondary',
    prefer_not_to_say: 'secondary',
};

function onPageChange(event) {
    router.get(route('people.index'), {
        ...props.filters,
        page: event.page + 1,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout :title="trans('people.title')">
        <Head :title="trans('people.title')" />

        <PageHeader
            :title="trans('people.title')"
            :subtitle="trans('people.subtitle')"
            :createRoute="'people.create'"
            :createLabel="trans('people.create')"
        />

        <FilterBar
            routeName="people.index"
            :searchPlaceholder="trans('people.search_placeholder')"
            :initialSearch="filters.search"
        />

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <EmptyState
                v-if="people.data.length === 0"
                icon="pi pi-users"
                :title="trans('people.empty')"
                :description="trans('people.empty_description')"
                :actionLabel="trans('people.create')"
                actionRoute="people.create"
            />

            <template v-else>
                <DataTable
                    :value="people.data"
                    :rowHover="true"
                    class="rounded-xl overflow-hidden"
                    tableClass="w-full"
                    stripedRows
                >
                    <Column field="id" :header="trans('common.id')" style="width: 80px" />
                    <Column field="name" :header="trans('people.fields.name')" />
                    <Column :header="trans('people.fields.cpf')">
                        <template #body="{ data }">
                            <span class="font-mono text-sm">{{ formatCpfDisplay(data.cpf) }}</span>
                        </template>
                    </Column>
                    <Column :header="trans('people.fields.gender')">
                        <template #body="{ data }">
                            <Tag
                                :value="trans('genders.' + data.gender)"
                                :severity="genderSeverity[data.gender] || 'secondary'"
                            />
                        </template>
                    </Column>
                    <Column :header="trans('people.fields.birth_date')">
                        <template #body="{ data }">
                            {{ formatDateDisplay(data.birth_date) }}
                        </template>
                    </Column>
                    <Column :header="trans('people.fields.phone')">
                        <template #body="{ data }">
                            {{ data.phone ? formatPhoneDisplay(data.phone) : '—' }}
                        </template>
                    </Column>
                    <Column :header="trans('people.fields.email')">
                        <template #body="{ data }">
                            {{ data.email || '—' }}
                        </template>
                    </Column>
                    <Column :header="trans('common.actions')" style="width: 80px">
                        <template #body="{ data }">
                            <Button
                                icon="pi pi-eye"
                                text
                                rounded
                                size="small"
                                severity="secondary"
                                :aria-label="trans('common.view')"
                                :title="trans('common.view')"
                                @click="router.visit(route('people.edit', data.id))"
                            />
                        </template>
                    </Column>
                </DataTable>

                <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ trans('common.showing') }} {{ people.from }} {{ trans('common.to') }} {{ people.to }} {{ trans('common.of') }} {{ people.total }} {{ trans('common.records') }}
                    </span>
                    <Paginator
                        :rows="people.per_page"
                        :totalRecords="people.total"
                        :first="(people.current_page - 1) * people.per_page"
                        template="PrevPageLink PageLinks NextPageLink"
                        class="border-none p-0"
                        @page="onPageChange"
                    />
                </div>
            </template>
        </div>
    </AppLayout>
</template>
