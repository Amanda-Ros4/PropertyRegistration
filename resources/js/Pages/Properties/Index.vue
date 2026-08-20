<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PropertyFilters from '@/Components/PropertyFilters.vue';
import EmptyState from '@/Components/EmptyState.vue';
import DeleteConfirmation from '@/Components/DeleteConfirmation.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Paginator from 'primevue/paginator';
import { formatCpfDisplay } from '@/utils/formatting';

const props = defineProps({
    properties: { type: Object, required: true },
    people: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const deleteConfirmRef = ref(null);
const propertyToDelete = ref(null);

const peopleOptions = computed(() =>
    props.people.map(p => ({
        value: p.id,
        label: `${p.name} — ${formatCpfDisplay(p.cpf)}`,
    }))
);

const hasActiveFilters = computed(() =>
    Boolean(
        props.filters.id
        || props.filters.type
        || props.filters.street
        || props.filters.number
        || props.filters.neighborhood
        || props.filters.person_id
        || props.filters.status,
    ),
);

function reportQuery() {
    return Object.fromEntries(
        Object.entries(props.filters).filter(([, value]) => value !== null && value !== ''),
    );
}

function openSyntheticReport() {
    window.open(route('properties.report.synthetic', reportQuery()), '_blank');
}

function onPageChange(event) {
    router.get(route('properties.index'), {
        ...Object.fromEntries(
            Object.entries(props.filters).filter(([, value]) => value !== null && value !== ''),
        ),
        page: event.page + 1,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function confirmDelete(property) {
    propertyToDelete.value = property;
    deleteConfirmRef.value?.open();
}

function deleteProperty() {
    if (!propertyToDelete.value) {
        return;
    }

    router.delete(route('properties.destroy', propertyToDelete.value.id), {
        preserveScroll: true,
        onFinish: () => {
            propertyToDelete.value = null;
        },
    });
}
</script>

<template>
    <AppLayout :title="trans('properties.title')">
        <Head :title="trans('properties.title')" />

        <PageHeader
            :title="trans('properties.title')"
            :subtitle="trans('properties.subtitle')"
            createRoute="properties.create"
            :createLabel="trans('properties.create')"
        >
            <template #actions>
                <Button
                    :label="trans('properties.reports.synthetic')"
                    icon="pi pi-file-pdf"
                    severity="secondary"
                    outlined
                    @click="openSyntheticReport"
                />
            </template>
        </PageHeader>

        <PropertyFilters
            :filters="filters"
            :peopleOptions="peopleOptions"
        />

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <EmptyState
                v-if="properties.data.length === 0"
                icon="pi pi-building"
                :title="hasActiveFilters ? trans('properties.empty_filtered') : trans('properties.empty')"
                :description="hasActiveFilters ? trans('properties.empty_filtered_description') : trans('properties.empty_description')"
                :actionLabel="hasActiveFilters ? null : trans('properties.create')"
                :actionRoute="hasActiveFilters ? null : 'properties.create'"
            />

            <template v-else>
                <DataTable
                    :value="properties.data"
                    :rowHover="true"
                    class="rounded-xl overflow-hidden"
                    stripedRows
                >
                    <Column :header="trans('properties.fields.municipal_registration')" style="width: 80px">
                        <template #body="{ data }">
                            <span class="font-mono font-medium text-indigo-600 dark:text-indigo-400">#{{ data.id }}</span>
                        </template>
                    </Column>
                    <Column :header="trans('properties.fields.type')" style="width: 120px">
                        <template #body="{ data }">
                            {{ data.type ? trans('properties.types.' + (data.type.value ?? data.type)) : '—' }}
                        </template>
                    </Column>
                    <Column field="street" :header="trans('properties.fields.street')" />
                    <Column field="number" :header="trans('properties.fields.number')" style="width: 100px" />
                    <Column field="neighborhood" :header="trans('properties.fields.neighborhood')" />
                    <Column :header="trans('properties.fields.owner')">
                        <template #body="{ data }">
                            <div v-if="data.person" class="flex flex-col">
                                <span class="font-medium">{{ data.person.name }}</span>
                                <span class="text-xs text-gray-400 font-mono">{{ formatCpfDisplay(data.person.cpf) }}</span>
                            </div>
                            <span v-else class="text-gray-400">—</span>
                        </template>
                    </Column>
                    <Column :header="trans('properties.fields.status')" style="width: 110px">
                        <template #body="{ data }">
                            <Tag
                                :value="trans('properties.statuses.' + (data.status?.value ?? data.status))"
                                :severity="(data.status?.value ?? data.status) === 'active' ? 'success' : 'secondary'"
                            />
                        </template>
                    </Column>
                    <Column :header="trans('common.actions')" style="width: 140px">
                        <template #body="{ data }">
                            <Button
                                icon="pi pi-eye"
                                text
                                rounded
                                size="small"
                                severity="secondary"
                                :aria-label="trans('common.view')"
                                :title="trans('common.view')"
                                @click="router.visit(route('properties.edit', data.id))"
                            />
                            <Button
                                icon="pi pi-trash"
                                text
                                rounded
                                size="small"
                                severity="danger"
                                :aria-label="trans('common.delete')"
                                :title="trans('common.delete')"
                                @click="confirmDelete(data)"
                            />
                        </template>
                    </Column>
                </DataTable>

                <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ trans('common.showing') }} {{ properties.from }} {{ trans('common.to') }} {{ properties.to }} {{ trans('common.of') }} {{ properties.total }} {{ trans('common.records') }}
                    </span>
                    <Paginator
                        :rows="properties.per_page"
                        :totalRecords="properties.total"
                        :first="(properties.current_page - 1) * properties.per_page"
                        @page="onPageChange"
                        template="PrevPageLink PageLinks NextPageLink"
                        class="border-none p-0"
                    />
                </div>
            </template>
        </div>

        <DeleteConfirmation
            ref="deleteConfirmRef"
            :title="trans('properties.delete_confirm_title')"
            :message="trans('properties.delete_confirm_message')"
            @confirm="deleteProperty"
        />
    </AppLayout>
</template>
