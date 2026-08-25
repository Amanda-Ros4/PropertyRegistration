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

const genderSeverity = {
    male: 'info',
    female: 'success',
    other: 'secondary',
    prefer_not_to_say: 'secondary',
};

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

        <PageHeader
            :title="trans('people.title')"
            :subtitle="trans('people.subtitle')"
            :createRoute="'people.create'"
            :createLabel="trans('people.create')"
        />

        <PersonFilters :filters="filters" />

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <EmptyState
                v-if="people.data.length === 0"
                icon="users"
                :title="trans('people.empty')"
                :description="trans('people.empty_description')"
                :actionLabel="trans('people.create')"
                actionRoute="people.create"
            />

            <template v-else>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-20">
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
                            <TableHead class="w-36 text-right">
                                {{ trans('common.actions') }}
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="person in people.data" :key="person.id">
                            <TableCell>{{ person.id }}</TableCell>
                            <TableCell>{{ person.name }}</TableCell>
                            <TableCell>
                                <span class="text-sm">{{ formatCpfDisplay(person.cpf) }}</span>
                            </TableCell>
                            <TableCell>
                                <StatusBadge
                                    :value="trans('genders.' + person.gender)"
                                    :severity="genderSeverity[person.gender] || 'secondary'"
                                />
                            </TableCell>
                            <TableCell>
                                {{ formatDateDisplay(person.birth_date) }}
                            </TableCell>
                            <TableCell>
                                {{ person.phone ? formatPhoneDisplay(person.phone) : '—' }}
                            </TableCell>
                            <TableCell>
                                {{ person.email || '—' }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-1 shrink-0">
                                    <TableIconButton
                                        icon="eye"
                                        :label="trans('common.view')"
                                        @click="router.visit(route('people.edit', person.id))"
                                    />
                                    <TableIconButton
                                        icon="trash"
                                        :label="trans('common.delete')"
                                        @click="confirmDelete(person)"
                                    />
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ trans('common.showing') }} {{ people.from }} {{ trans('common.to') }} {{ people.to }} {{ trans('common.of') }} {{ people.total }} {{ trans('common.records') }}
                    </span>
                    <InertiaPagination
                        :paginator="people"
                        route-name="people.index"
                        :query="paginationQuery"
                    />
                </div>
            </template>
        </div>

        <DeleteConfirmation
            ref="deleteConfirmRef"
            :title="trans('people.delete_confirm_title')"
            :message="deleteConfirmMessage"
            @confirm="deletePerson"
        />
    </AppLayout>
</template>
