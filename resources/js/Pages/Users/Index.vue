<script setup>
import { computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Eye, Pencil } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import FilterBar from '@/Components/FilterBar.vue';
import EmptyState from '@/Components/EmptyState.vue';
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

        <PageHeader
            :title="trans('users.title')"
            :subtitle="trans('users.subtitle')"
            :createRoute="showCreateButton ? 'users.create' : null"
            :createLabel="trans('users.create')"
        />

        <FilterBar
            routeName="users.index"
            :heading="trans('users.filters.heading')"
            :searchPlaceholder="trans('users.search_placeholder')"
            :initialSearch="filters.search"
        />

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <EmptyState
                v-if="users.data.length === 0"
                icon="user"
                :title="trans('users.empty')"
                :description="trans('users.empty_description')"
                :actionLabel="showCreateButton ? trans('users.create') : null"
                :actionRoute="showCreateButton ? 'users.create' : null"
            />

            <template v-else>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-20">
                                {{ trans('common.id') }}
                            </TableHead>
                            <TableHead>
                                {{ trans('users.fields.name') }}
                            </TableHead>
                            <TableHead>
                                {{ trans('users.fields.email') }}
                            </TableHead>
                            <TableHead>
                                {{ trans('users.fields.profile') }}
                            </TableHead>
                            <TableHead>
                                {{ trans('users.fields.active') }}
                            </TableHead>
                            <TableHead class="w-[100px]">
                                {{ trans('common.actions') }}
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in users.data" :key="user.id">
                            <TableCell>{{ user.id }}</TableCell>
                            <TableCell>{{ user.name }}</TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell>
                                <StatusBadge
                                    :value="trans(profileLabelKey[user.profile] || 'users.profiles.attendant')"
                                    :severity="profileSeverity[user.profile] || 'secondary'"
                                />
                            </TableCell>
                            <TableCell>
                                <StatusBadge
                                    :value="trans(activeLabelKey[user.active] || 'users.active_status.inactive')"
                                    :severity="activeSeverity[user.active] || 'secondary'"
                                />
                            </TableCell>
                            <TableCell>
                                <Button
                                    variant="ghost"
                                    size="icon-sm"
                                    :aria-label="user.can_update ? trans('common.edit') : trans('common.view')"
                                    :title="user.can_update ? trans('common.edit') : trans('common.view')"
                                    @click="router.visit(route('users.edit', user.id))"
                                >
                                    <Pencil v-if="user.can_update" class="size-4" />
                                    <Eye v-else class="size-4" />
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ trans('common.showing') }} {{ users.from }} {{ trans('common.to') }} {{ users.to }} {{ trans('common.of') }} {{ users.total }} {{ trans('common.records') }}
                    </span>
                    <InertiaPagination
                        :paginator="users"
                        route-name="users.index"
                        :query="paginationQuery"
                    />
                </div>
            </template>
        </div>
    </AppLayout>
</template>
