<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import TableIconButton from '@/Components/TableIconButton.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PersonFilters from '@/Components/PersonFilters.vue';
import EmptyState from '@/Components/EmptyState.vue';
import DeleteConfirmation from '@/Components/DeleteConfirmation.vue';
import InertiaPagination from '@/Components/InertiaPagination.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { formatCpfDisplay, formatDateDisplay, formatPhoneDisplay } from '@/utils/formatting';

const props = defineProps({
    people: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const deleteConfirmRef = ref(null);
const personToDelete = ref(null);

const paginationQuery = computed(() =>
    Object.fromEntries(
        Object.entries(props.filters).filter(([, value]) => value !== null && value !== ''),
    ),
);

const deleteConfirmMessage = computed(() => {
    if (!personToDelete.value) {
        return trans('people.delete_confirm_message');
    }

    return trans('people.delete_confirm_message_detail', {
        name: personToDelete.value.name,
    });
});

function confirmDelete(person) {
    personToDelete.value = person;
    deleteConfirmRef.value?.open();
}

function deletePerson() {
    if (!personToDelete.value) {
        return;
    }

    router.delete(route('people.destroy', personToDelete.value.id), {
        preserveScroll: true,
        onFinish: () => {
            personToDelete.value = null;
        },
    });
}
</script>

<template>
    <AppLayout :title="trans('people.title')">

        <Head :title="trans('people.title')" />

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Cabeçalho -->
            <PageHeader :title="trans('people.title')" :subtitle="trans('people.subtitle')"
                :createRoute="'people.create'" :createLabel="trans('people.create')" />

            <!-- Card dos Filtros -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                <PersonFilters :filters="filters" />
            </div>

            <!-- Card da Tabela -->
            <div
                class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6">
                <EmptyState v-if="people.data.length === 0" icon="users" :title="trans('people.empty')"
                    :description="trans('people.empty_description')" :actionLabel="trans('people.create')"
                    actionRoute="people.create" />

                <template v-else>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-16">
                                    {{ trans('common.id') }}
                                </TableHead>
                                <TableHead>
                                    {{ trans('people.fields.name') }}
                                </TableHead>
                                <TableHead>
                                    {{ trans('people.fields.cpf') }}
                                </TableHead>
                                <TableHead>
                                    {{ trans('people.fields.gender') }}
                                </TableHead>
                                <TableHead>
                                    {{ trans('people.fields.birth_date') }}
                                </TableHead>
                                <TableHead>
                                    {{ trans('people.fields.phone') }}
                                </TableHead>
                                <TableHead>
                                    {{ trans('people.fields.email') }}
                                </TableHead>
                                <TableHead class="w-28 text-right">
                                    {{ trans('common.actions') }}
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="person in people.data" :key="person.id">
                                <TableCell class="w-16 font-medium">{{ person.id }}</TableCell>
                                <TableCell>{{ person.name }}</TableCell>
                                <TableCell>
                                    <span class="text-sm">{{ formatCpfDisplay(person.cpf) }}</span>
                                </TableCell>
                                <TableCell>
                                    {{ person.gender ? trans('genders.' + person.gender) : '—' }}
                                </TableCell>
                                <TableCell>
                                    {{ formatDateDisplay(person.birth_date) }}
                                </TableCell>
                                <TableCell>
                                    <span v-if="person.phone">{{ formatPhoneDisplay(person.phone) }}</span>
                                    <span v-else class="text-slate-300 dark:text-slate-700">—</span>
                                </TableCell>
                                <TableCell>
                                    <span v-if="person.email">{{ person.email }}</span>
                                    <span v-else class="text-slate-300 dark:text-slate-700">—</span>
                                </TableCell>
                                <TableCell class="w-28 text-right">
                                    <div class="flex items-center justify-end gap-1 shrink-0">
                                        <TableIconButton icon="eye" :label="trans('common.view')"
                                            @click="router.visit(route('people.edit', person.id))" />
                                        <TableIconButton icon="trash" :label="trans('common.delete')"
                                            @click="confirmDelete(person)" />
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div
                        class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-sm text-slate-500 dark:text-slate-400">
                            {{ trans('common.showing') }} {{ people.from }} {{ trans('common.to') }} {{ people.to }} {{
                                trans('common.of') }} {{ people.total }} {{ trans('common.records') }}
                        </span>
                        <InertiaPagination :paginator="people" route-name="people.index" :query="paginationQuery" />
                    </div>
                </template>
            </div>

            <DeleteConfirmation ref="deleteConfirmRef" :title="trans('people.delete_confirm_title')"
                :message="deleteConfirmMessage" @confirm="deletePerson" />
        </div>
    </AppLayout>
</template>