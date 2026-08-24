<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Eye, FileText, Trash2 } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PropertyFilters from '@/Components/PropertyFilters.vue';
import EmptyState from '@/Components/EmptyState.vue';
import DeleteConfirmation from '@/Components/DeleteConfirmation.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import InertiaPagination from '@/Components/InertiaPagination.vue';
import { Button } from '@/Components/ui/button';
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

        <PageHeader
            :title="trans('properties.title')"
            :subtitle="trans('properties.subtitle')"
            createRoute="properties.create"
            :createLabel="trans('properties.create')"
        >
            <template #actions>
                <Button variant="outline" @click="openSyntheticReport">
                    <FileText class="size-4" />
                    {{ trans('properties.reports.synthetic') }}
                </Button>
            </template>
        </PageHeader>

        <PropertyFilters
            :filters="filters"
            :peopleOptions="peopleOptions"
        />

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <EmptyState
                v-if="properties.data.length === 0"
                icon="building"
                :title="hasActiveFilters ? trans('properties.empty_filtered') : trans('properties.empty')"
                :description="hasActiveFilters ? trans('properties.empty_filtered_description') : trans('properties.empty_description')"
                :actionLabel="hasActiveFilters ? null : trans('properties.create')"
                :actionRoute="hasActiveFilters ? null : 'properties.create'"
            />

            <template v-else>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-20">
                                {{ trans('properties.fields.municipal_registration') }}
                            </TableHead>
                            <TableHead class="w-[120px]">
                                {{ trans('properties.fields.type') }}
                            </TableHead>
                            <TableHead>
                                {{ trans('properties.fields.street') }}
                            </TableHead>
                            <TableHead class="w-[100px]">
                                {{ trans('properties.fields.number') }}
                            </TableHead>
                            <TableHead>
                                {{ trans('properties.fields.neighborhood') }}
                            </TableHead>
                            <TableHead>
                                {{ trans('properties.fields.owner') }}
                            </TableHead>
                            <TableHead class="w-[110px]">
                                {{ trans('properties.fields.cep') }}
                            </TableHead>
                            <TableHead>
                                {{ trans('properties.fields.complement') }}
                            </TableHead>
                            <TableHead class="w-[110px]">
                                {{ trans('properties.fields.status') }}
                            </TableHead>
                            <TableHead class="w-[140px]">
                                {{ trans('common.actions') }}
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="property in properties.data" :key="property.id">
                            <TableCell>
                                <span class="font-medium text-indigo-600 dark:text-indigo-400">#{{ property.id }}</span>
                            </TableCell>
                            <TableCell>
                                {{ property.type ? trans('properties.types.' + (property.type.value ?? property.type)) : '—' }}
                            </TableCell>
                            <TableCell>{{ property.street }}</TableCell>
                            <TableCell>{{ property.number }}</TableCell>
                            <TableCell>{{ property.neighborhood }}</TableCell>
                            <TableCell>
                                <div v-if="property.person" class="flex flex-col">
                                    <span class="font-medium">{{ property.person.name }}</span>
                                    <span class="text-xs text-gray-400">{{ formatCpfDisplay(property.person.cpf) }}</span>
                                </div>
                                <span v-else class="text-gray-400">—</span>
                            </TableCell>
                            <TableCell>
                                <span class="text-sm">{{ property.cep ? formatCepDisplay(property.cep) : '—' }}</span>
                            </TableCell>
                            <TableCell>
                                {{ property.complement || '—' }}
                            </TableCell>
                            <TableCell>
                                <StatusBadge
                                    :value="trans('properties.statuses.' + (property.status?.value ?? property.status))"
                                    :severity="(property.status?.value ?? property.status) === 'active' ? 'success' : 'secondary'"
                                />
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
                                        :aria-label="trans('common.view')"
                                        :title="trans('common.view')"
                                        @click="router.visit(route('properties.edit', property.id))"
                                    >
                                        <Eye class="size-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
                                        class="text-destructive hover:text-destructive"
                                        :aria-label="trans('common.delete')"
                                        :title="trans('common.delete')"
                                        @click="confirmDelete(property)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ trans('common.showing') }} {{ properties.from }} {{ trans('common.to') }} {{ properties.to }} {{ trans('common.of') }} {{ properties.total }} {{ trans('common.records') }}
                    </span>
                    <InertiaPagination
                        :paginator="properties"
                        route-name="properties.index"
                        :query="paginationQuery"
                    />
                </div>
            </template>
        </div>

        <DeleteConfirmation
            ref="deleteConfirmRef"
            :title="trans('properties.delete_confirm_title')"
            :message="deleteConfirmMessage"
            @confirm="deleteProperty"
        />
    </AppLayout>
</template>
