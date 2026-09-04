<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import TableIconButton from '@/Components/TableIconButton.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PropertyFilters from '@/Components/PropertyFilters.vue';
import EmptyState from '@/Components/EmptyState.vue';
import DeleteConfirmation from '@/Components/DeleteConfirmation.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import InertiaPagination from '@/Components/InertiaPagination.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { formatCepDisplay, formatCpfDisplay } from '@/utils/formatting';

const props = defineProps({
    properties: { type: Object, required: true },
    people: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const deleteConfirmRef = ref(null);
const propertyToDelete = ref(null);

const paginationQuery = computed(() =>
    Object.fromEntries(
        Object.entries(props.filters).filter(([, value]) => value !== null && value !== ''),
    ),
);

const deleteConfirmMessage = computed(() => {
    if (!propertyToDelete.value) {
        return trans('properties.delete_confirm_message');
    }

    return trans('properties.delete_confirm_message_detail', {
        registration: propertyToDelete.value.id,
    });
});

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

function openSyntheticReport() {
    window.open(route('properties.report.synthetic', paginationQuery.value), '_blank');
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

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Cabeçalho -->
            <PageHeader :title="trans('properties.title')" :subtitle="trans('properties.subtitle')"
                createRoute="properties.create" :createLabel="trans('properties.create')">
                <template #actions>
                    <SecondaryButton type="button" @click="openSyntheticReport">
                        {{ trans('properties.reports.synthetic') }}
                    </SecondaryButton>
                </template>
            </PageHeader>

            <!-- Card dos Filtros -->
            <div
                class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                <PropertyFilters :filters="filters" :peopleOptions="peopleOptions" />
            </div>

            <!-- Card da Tabela -->
            <div
                class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6">
                <EmptyState v-if="properties.data.length === 0" icon="building"
                    :title="hasActiveFilters ? trans('properties.empty_filtered') : trans('properties.empty')"
                    :description="hasActiveFilters ? trans('properties.empty_filtered_description') : trans('properties.empty_description')"
                    :actionLabel="hasActiveFilters ? null : trans('properties.create')"
                    :actionRoute="hasActiveFilters ? null : 'properties.create'" />

                <template v-else>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-24">
                                    {{ trans('properties.fields.municipal_registration') }}
                                </TableHead>
                                <TableHead>
                                    {{ trans('properties.fields.type') }}
                                </TableHead>
                                <TableHead>
                                    {{ trans('properties.fields.street') }}
                                </TableHead>
                                <TableHead>
                                    {{ trans('properties.fields.number') }}
                                </TableHead>
                                <TableHead>
                                    {{ trans('properties.fields.neighborhood') }}
                                </TableHead>
                                <TableHead>
                                    {{ trans('properties.fields.owner') }}
                                </TableHead>
                                <TableHead>
                                    {{ trans('properties.fields.cep') }}
                                </TableHead>
                                <TableHead>
                                    {{ trans('properties.fields.complement') }}
                                </TableHead>
                                <TableHead>
                                    {{ trans('properties.fields.status') }}
                                </TableHead>
                                <TableHead class="w-28 text-right">
                                    {{ trans('common.actions') }}
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="property in properties.data" :key="property.id">
                                <TableCell class="w-24 font-medium">#{{ property.id }}</TableCell>
                                <TableCell>
                                    {{ property.type ? trans('properties.types.' + (property.type.value ??
                                    property.type)) : '—' }}
                                </TableCell>
                                <TableCell>{{ property.street }}</TableCell>
                                <TableCell>{{ property.number }}</TableCell>
                                <TableCell>{{ property.neighborhood }}</TableCell>
                                <TableCell>
                                    <div v-if="property.person" class="flex flex-col">
                                        <span class="font-medium">{{ property.person.name }}</span>
                                        <span class="text-xs text-slate-400">{{ formatCpfDisplay(property.person.cpf)
                                            }}</span>
                                    </div>
                                    <span v-else class="text-slate-300 dark:text-slate-700">—</span>
                                </TableCell>
                                <TableCell>
                                    <span class="text-sm">{{ property.cep ? formatCepDisplay(property.cep) : '—'
                                        }}</span>
                                </TableCell>
                                <TableCell>
                                    {{ property.complement || '—' }}
                                </TableCell>
                                <TableCell>
                                    <StatusBadge
                                        :value="trans('properties.statuses.' + (property.status?.value ?? property.status))"
                                        :severity="(property.status?.value ?? property.status) === 'active' ? 'success' : 'secondary'" />
                                </TableCell>
                                <TableCell class="w-28 text-right">
                                    <div class="flex items-center justify-end gap-1 shrink-0">
                                        <TableIconButton icon="eye" :label="trans('common.view')"
                                            @click="router.visit(route('properties.edit', property.id))" />
                                        <TableIconButton icon="trash" :label="trans('common.delete')"
                                            @click="confirmDelete(property)" />
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-sm text-slate-500 dark:text-slate-400">
                            {{ trans('common.showing') }} {{ properties.from }} {{ trans('common.to') }} {{
                            properties.to }} {{
                                trans('common.of') }} {{ properties.total }} {{ trans('common.records') }}
                        </span>
                        <InertiaPagination :paginator="properties" route-name="properties.index"
                            :query="paginationQuery" />
                    </div>
                </template>
            </div>

            <DeleteConfirmation ref="deleteConfirmRef" :title="trans('properties.delete_confirm_title')"
                :message="deleteConfirmMessage" @confirm="deleteProperty" />
        </div>
    </AppLayout>
</template>