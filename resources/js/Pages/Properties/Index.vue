<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import FilterBar from '@/Components/FilterBar.vue';
import EmptyState from '@/Components/EmptyState.vue';
import DeleteConfirmation from '@/Components/DeleteConfirmation.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Paginator from 'primevue/paginator';
import { formatCepDisplay, formatCpfDisplay } from '@/utils/formatting';

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

function confirmDelete(property) {
    propertyToDelete.value = property;
    deleteConfirmRef.value.open();
}

function deleteProperty() {
    router.delete(route('properties.destroy', propertyToDelete.value.id), {
        preserveScroll: true,
    });
}

function onPageChange(event) {
    router.get(route('properties.index'), {
        ...props.filters,
        page: event.page + 1,
    }, {
        preserveState: true,
        preserveScroll: true,
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
        />

        <FilterBar
            routeName="properties.index"
            :searchPlaceholder="trans('properties.search_placeholder')"
            :initialSearch="filters.search"
            :selectOptions="peopleOptions"
            selectOptionLabel="label"
            selectOptionValue="value"
            :selectPlaceholder="trans('properties.filter_owner')"
            :initialSelectValue="filters.person_id ? Number(filters.person_id) : null"
            selectFilterKey="person_id"
        />

        <DeleteConfirmation
            ref="deleteConfirmRef"
            :title="trans('properties.delete_confirm_title')"
            :message="trans('properties.delete_confirm_message')"
            :acceptLabel="trans('common.delete')"
            :rejectLabel="trans('common.cancel')"
            @confirm="deleteProperty"
        />

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <EmptyState
                v-if="properties.data.length === 0"
                icon="pi pi-building"
                :title="trans('properties.empty')"
                :description="trans('properties.empty_description')"
                :actionLabel="trans('properties.create')"
                actionRoute="properties.create"
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
                    <Column :header="trans('properties.fields.owner')">
                        <template #body="{ data }">
                            <div v-if="data.person" class="flex flex-col">
                                <span class="font-medium">{{ data.person.name }}</span>
                                <span class="text-xs text-gray-400 font-mono">{{ formatCpfDisplay(data.person.cpf) }}</span>
                            </div>
                            <span v-else class="text-gray-400">—</span>
                        </template>
                    </Column>
                    <Column :header="trans('properties.fields.cep')" style="width: 110px">
                        <template #body="{ data }">
                            <span class="font-mono text-sm">{{ data.cep ? formatCepDisplay(data.cep) : '—' }}</span>
                        </template>
                    </Column>
                    <Column field="street" :header="trans('properties.fields.street')" />
                    <Column field="number" :header="trans('properties.fields.number')" style="width: 100px" />
                    <Column field="neighborhood" :header="trans('properties.fields.neighborhood')" />
                    <Column :header="trans('properties.fields.complement')">
                        <template #body="{ data }">
                            {{ data.complement || '—' }}
                        </template>
                    </Column>
                    <Column :header="trans('common.actions')" style="width: 120px">
                        <template #body="{ data }">
                            <div class="flex items-center gap-1">
                                <Button
                                    icon="pi pi-pencil"
                                    text
                                    rounded
                                    size="small"
                                    severity="secondary"
                                    :aria-label="trans('common.edit')"
                                    @click="router.visit(route('properties.edit', data.id))"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    text
                                    rounded
                                    size="small"
                                    severity="danger"
                                    :aria-label="trans('common.delete')"
                                    @click="confirmDelete(data)"
                                />
                            </div>
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
    </AppLayout>
</template>
