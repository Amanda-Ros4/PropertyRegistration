<script setup>
import { computed, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import FilterBar from '@/Components/FilterBar.vue';
import EmptyState from '@/Components/EmptyState.vue';
import DeleteConfirmation from '@/Components/DeleteConfirmation.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Paginator from 'primevue/paginator';
import { formatCpfDisplay } from '@/utils/formatting';

const props = defineProps({
    users: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    canCreate: { type: Boolean, default: false },
});

const page = usePage();
const deleteConfirmRef = ref(null);
const userToDelete = ref(null);

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

function confirmDelete(user) {
    userToDelete.value = user;
    deleteConfirmRef.value?.open();
}

function deleteUser() {
    router.delete(route('users.destroy', userToDelete.value.id), {
        preserveScroll: true,
    });
}

function onPageChange(event) {
    router.get(route('users.index'), {
        ...props.filters,
        page: event.page + 1,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}
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
            :searchPlaceholder="trans('users.search_placeholder')"
            :initialSearch="filters.search"
        />

        <DeleteConfirmation
            ref="deleteConfirmRef"
            :title="trans('users.delete_confirm_title')"
            :message="trans('users.delete_confirm_message')"
            :acceptLabel="trans('common.delete')"
            :rejectLabel="trans('common.cancel')"
            @confirm="deleteUser"
        />

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <EmptyState
                v-if="users.data.length === 0"
                icon="pi pi-user"
                :title="trans('users.empty')"
                :description="trans('users.empty_description')"
                :actionLabel="showCreateButton ? trans('users.create') : null"
                :actionRoute="showCreateButton ? 'users.create' : null"
            />

            <template v-else>
                <DataTable
                    :value="users.data"
                    :rowHover="true"
                    class="rounded-xl overflow-hidden"
                    tableClass="w-full"
                    stripedRows
                >
                    <Column field="name" :header="trans('users.fields.name')" />
                    <Column field="email" :header="trans('users.fields.email')" />
                    <Column :header="trans('users.fields.cpf')">
                        <template #body="{ data }">
                            <span class="font-mono text-sm">{{ data.cpf ? formatCpfDisplay(data.cpf) : '—' }}</span>
                        </template>
                    </Column>
                    <Column :header="trans('users.fields.profile')">
                        <template #body="{ data }">
                            <Tag
                                :value="trans(profileLabelKey[data.profile] || 'users.profiles.attendant')"
                                :severity="profileSeverity[data.profile] || 'secondary'"
                            />
                        </template>
                    </Column>
                    <Column :header="trans('users.fields.active')">
                        <template #body="{ data }">
                            <Tag
                                :value="trans(activeLabelKey[data.active] || 'users.active_status.inactive')"
                                :severity="activeSeverity[data.active] || 'secondary'"
                            />
                        </template>
                    </Column>
                    <Column :header="trans('common.actions')" style="width: 120px">
                        <template #body="{ data }">
                            <div v-if="data.can_update || data.can_delete" class="flex items-center gap-1">
                                <Button
                                    v-if="data.can_update"
                                    icon="pi pi-pencil"
                                    text
                                    rounded
                                    size="small"
                                    severity="secondary"
                                    :aria-label="trans('common.edit')"
                                    :title="trans('common.edit')"
                                    @click="router.visit(route('users.edit', data.id))"
                                />
                                <Button
                                    v-if="data.can_delete"
                                    icon="pi pi-trash"
                                    text
                                    rounded
                                    size="small"
                                    severity="danger"
                                    :aria-label="trans('common.delete')"
                                    :title="trans('common.delete')"
                                    @click="confirmDelete(data)"
                                />
                            </div>
                            <span v-else class="text-sm text-slate-400">—</span>
                        </template>
                    </Column>
                </DataTable>

                <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ trans('common.showing') }} {{ users.from }} {{ trans('common.to') }} {{ users.to }} {{ trans('common.of') }} {{ users.total }} {{ trans('common.records') }}
                    </span>
                    <Paginator
                        :rows="users.per_page"
                        :totalRecords="users.total"
                        :first="(users.current_page - 1) * users.per_page"
                        template="PrevPageLink PageLinks NextPageLink"
                        class="border-none p-0"
                        @page="onPageChange"
                    />
                </div>
            </template>
        </div>
    </AppLayout>
</template>
