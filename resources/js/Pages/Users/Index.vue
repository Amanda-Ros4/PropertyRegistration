<script setup>
import { computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import TableIconButton from '@/Components/TableIconButton.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import FilterBar from '@/Components/FilterBar.vue';
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
    users: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    canCreate: { type: Boolean, default: false },
});

const page = usePage();

const paginationQuery = computed(() =>
    Object.fromEntries(
        Object.entries(props.filters).filter(([, value]) => value !== null && value !== ''),
    ),
);

const profileSeverity = {
    T: 'danger',
    S: 'warn',
    A: 'info',
};

const activeSeverity = {
    S: 'success',
    N: 'secondary',
};

const profileLabelKey = {
    T: 'users.profiles.ti_admin',
    S: 'users.profiles.system_admin',
    A: 'users.profiles.attendant',
};

const activeLabelKey = {
    S: 'users.active_status.active',
    N: 'users.active_status.inactive',
};

const showCreateButton = computed(() => props.canCreate && page.props.permissions?.canManageUsers);
</script>

<template>
    <AppLayout :title="trans('users.title')">

        <Head :title="trans('users.title')" />

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Cabeçalho -->
            <PageHeader :title="trans('users.title')" :subtitle="trans('users.subtitle')"
                :createRoute="showCreateButton ? 'users.create' : null" :createLabel="trans('users.create')" />

            <!-- Card dos Filtros encapsulado em sua própria div, igual a Imóveis -->
            <div
                class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                <FilterBar routeName="users.index" :heading="trans('users.filters.heading')"
                    :searchPlaceholder="trans('users.search_placeholder')" :initialSearch="filters.search" />
            </div>

            <!-- Card da Tabela com as cores slate -->
            <div
                class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6">
                <EmptyState v-if="users.data.length === 0" icon="user" :title="trans('users.empty')"
                    :description="trans('users.empty_description')"
                    :actionLabel="showCreateButton ? trans('users.create') : null"
                    :actionRoute="showCreateButton ? 'users.create' : null" />

                <template v-else>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-16">{{ trans('common.id') }}</TableHead>


                                <TableHead class="w-[30%]">{{ trans('users.fields.name') }}</TableHead>
                                <TableHead class="w-[30%]">{{ trans('users.fields.email') }}</TableHead>
                                <TableHead class="w-56">{{ trans('users.fields.profile') }}</TableHead>
                                <TableHead class="w-32">{{ trans('users.fields.active') }}</TableHead>
                                <TableHead class="w-24 text-right">
                                    <span class="mr-1">{{ trans('common.actions') }}</span>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in users.data" :key="user.id">
                                <TableCell class="font-medium">{{ user.id }}</TableCell>
                                <TableCell>{{ user.name }}</TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>
                                    <StatusBadge
                                        :value="trans(profileLabelKey[user.profile] || 'users.profiles.attendant')"
                                        :severity="profileSeverity[user.profile] || 'secondary'" />
                                </TableCell>
                                <TableCell>
                                    <StatusBadge
                                        :value="trans(activeLabelKey[user.active] || 'users.active_status.inactive')"
                                        :severity="activeSeverity[user.active] || 'secondary'" />
                                </TableCell>
                                <TableCell class="w-24">
                                    <!-- justify-end mantém na direita, e mr-2 empurra o ícone para o centro da palavra acima -->
                                    <div class="flex items-center justify-end mr-2 gap-1 shrink-0">
                                        <TableIconButton :icon="user.can_update ? 'pencil' : 'eye'"
                                            :label="user.can_update ? trans('common.edit') : trans('common.view')"
                                            @click="router.visit(route('users.edit', user.id))" />
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-sm text-slate-500 dark:text-slate-400">
                            {{ trans('common.showing') }} {{ users.from }} {{ trans('common.to') }} {{ users.to }} {{
                                trans('common.of') }} {{ users.total }} {{ trans('common.records') }}
                        </span>
                        <InertiaPagination :paginator="users" route-name="users.index" :query="paginationQuery" />
                    </div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>