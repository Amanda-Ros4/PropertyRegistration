<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { formatDateTimeDisplay } from '@/utils/formatting';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import Tag from 'primevue/tag';

const props = defineProps({
    audit: { type: Object, required: true },
});

const page = usePage();

const pageTitle = computed(() => trans('audit.details_title', { id: props.audit.id }));

const eventSeverity = {
    created: 'success',
    updated: 'info',
    deleted: 'danger',
    restored: 'warn',
};

function formatDateTime(value) {
    return formatDateTimeDisplay(value, page.props.locale);
}

function eventLabel(event) {
    return event ? trans(`audit.events.${event}`) : '—';
}

function tableLabel(labelKey) {
    return labelKey ? trans(labelKey) : '—';
}
</script>

<template>
    <AppLayout :title="pageTitle">
        <Head :title="pageTitle" />

        <PageHeader
            :title="pageTitle"
            :subtitle="trans('audit.details_subtitle')"
            backRoute="audit.index"
            :backLabel="trans('common.back')"
        />

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <dl class="divide-y divide-gray-100 dark:divide-gray-800">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('audit.fields.id') }}</dt>
                    <dd class="sm:col-span-2 font-mono text-sm">#{{ audit.id }}</dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('audit.fields.datetime') }}</dt>
                    <dd class="sm:col-span-2 font-mono text-sm">{{ formatDateTime(audit.created_at) }}</dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('audit.fields.user') }}</dt>
                    <dd class="sm:col-span-2">
                        <div class="font-medium">{{ audit.user_name }}</div>
                        <div class="text-sm text-gray-400">{{ audit.user_email || '—' }}</div>
                    </dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('audit.fields.event') }}</dt>
                    <dd class="sm:col-span-2">
                        <Tag
                            :value="eventLabel(audit.event)"
                            :severity="eventSeverity[audit.event] || 'secondary'"
                        />
                    </dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('audit.fields.table') }}</dt>
                    <dd class="sm:col-span-2">{{ tableLabel(audit.table_label_key) }}</dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('audit.fields.audited_id') }}</dt>
                    <dd class="sm:col-span-2 font-mono text-sm">{{ audit.auditable_id ?? '—' }}</dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('audit.fields.old_values') }}</dt>
                    <dd class="sm:col-span-2">
                        <pre
                            v-if="audit.old_values"
                            class="overflow-x-auto rounded-lg bg-gray-50 dark:bg-gray-950 p-4 text-xs font-mono whitespace-pre-wrap"
                        >{{ audit.old_values }}</pre>
                        <span v-else>—</span>
                    </dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('audit.fields.new_values') }}</dt>
                    <dd class="sm:col-span-2">
                        <pre
                            v-if="audit.new_values"
                            class="overflow-x-auto rounded-lg bg-gray-50 dark:bg-gray-950 p-4 text-xs font-mono whitespace-pre-wrap"
                        >{{ audit.new_values }}</pre>
                        <span v-else>—</span>
                    </dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('audit.fields.url') }}</dt>
                    <dd class="sm:col-span-2 break-all text-sm">{{ audit.url || '—' }}</dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-6 py-4">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ trans('audit.fields.ip') }}</dt>
                    <dd class="sm:col-span-2 font-mono text-sm">{{ audit.ip_address || '—' }}</dd>
                </div>
            </dl>
        </div>
    </AppLayout>
</template>
