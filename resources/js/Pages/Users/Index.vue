<script setup>
import { Head } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import EmptyState from '@/Components/EmptyState.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { formatCpfDisplay } from '@/utils/formatting';

defineProps({
    users: { type: Array, default: () => [] },
});
</script>

<template>
    <AppLayout :title="trans('users.title')">
        <Head :title="trans('users.title')" />

        <PageHeader
            :title="trans('users.title')"
            :subtitle="trans('users.subtitle')"
        />

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <EmptyState
                v-if="users.length === 0"
                icon="pi pi-user"
                :title="trans('users.empty')"
                :description="trans('users.empty_description')"
            />

            <DataTable
                v-else
                :value="users"
                :rowHover="true"
                class="rounded-xl overflow-hidden"
                stripedRows
            >
                <Column field="name" :header="trans('common.name')" />
                <Column field="email" :header="trans('common.email')" />
                <Column :header="trans('people.fields.cpf')">
                    <template #body="{ data }">
                        <span class="font-mono text-sm">{{ data.cpf ? formatCpfDisplay(data.cpf) : '—' }}</span>
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>
