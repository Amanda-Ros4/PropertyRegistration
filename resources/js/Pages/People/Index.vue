<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import EmptyState from '@/Components/EmptyState.vue';
import DeleteConfirmation from '@/Components/DeleteConfirmation.vue';
import BirthDateInput from '@/Components/BirthDateInput.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Paginator from 'primevue/paginator';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import {
    formatBirthDateForSubmit,
    formatCpfDisplay,
    formatCpfInput,
    formatPhoneDisplay,
    toBirthDateInputValue,
} from '@/utils/formatting';

const props = defineProps({
    people: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const deleteConfirmRef = ref(null);
const personToDelete = ref(null);

const name = ref(props.filters.name ?? '');
const cpf = ref(props.filters.cpf ? formatCpfInput(props.filters.cpf) : '');
const birthDate = ref(
    props.filters.birth_date ? toBirthDateInputValue(props.filters.birth_date) : '',
);
const gender = ref(props.filters.gender ?? null);

let debounceTimer = null;

const genderOptions = computed(() => [
    { value: 'male', label: trans('genders.male') },
    { value: 'female', label: trans('genders.female') },
    { value: 'other', label: trans('genders.other') },
    { value: 'prefer_not_to_say', label: trans('genders.prefer_not_to_say') },
]);

const genderSeverity = {
    male: 'info',
    female: 'success',
    other: 'secondary',
    prefer_not_to_say: 'secondary',
};

const hasActiveFilters = computed(() =>
    Boolean(name.value || cpf.value || birthDate.value || gender.value),
);

function buildFilterParams() {
    const params = {};
    if (name.value.trim()) params.name = name.value.trim();
    const cpfDigits = String(cpf.value).replace(/\D/g, '');
    if (cpfDigits) params.cpf = cpfDigits;
    const isoBirthDate = formatBirthDateForSubmit(birthDate.value);
    if (isoBirthDate) params.birth_date = isoBirthDate;
    if (gender.value) params.gender = gender.value;
    return params;
}

function applyFilters() {
    router.get(route('people.index'), buildFilterParams(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function scheduleFilter() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 400);
}

watch(name, scheduleFilter);
watch(cpf, scheduleFilter);
watch(birthDate, scheduleFilter);
watch(gender, applyFilters);

function onCpfInput(value) {
    cpf.value = formatCpfInput(value);
}

function clearFilters() {
    name.value = '';
    cpf.value = '';
    birthDate.value = '';
    gender.value = null;
    router.get(route('people.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function confirmDelete(person) {
    personToDelete.value = person;
    deleteConfirmRef.value.open();
}

function deletePerson() {
    router.delete(route('people.destroy', personToDelete.value.id), {
        preserveScroll: true,
    });
}

function onPageChange(event) {
    router.get(route('people.index'), {
        ...buildFilterParams(),
        page: event.page + 1,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString();
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

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3 mb-6">
            <InputText
                v-model="name"
                :placeholder="trans('people.fields.name')"
                class="w-full"
            />
            <InputText
                :modelValue="cpf"
                :placeholder="trans('people.fields.cpf')"
                class="w-full font-mono"
                @update:model-value="onCpfInput"
            />
            <BirthDateInput
                v-model="birthDate"
                :placeholder="trans('people.fields.birth_date')"
            />
            <Select
                v-model="gender"
                :options="genderOptions"
                optionLabel="label"
                optionValue="value"
                :placeholder="trans('people.fields.gender')"
                showClear
                class="w-full"
            />
            <Button
                v-if="hasActiveFilters"
                :label="trans('common.clear')"
                icon="pi pi-filter-slash"
                outlined
                severity="secondary"
                class="w-full"
                @click="clearFilters"
            />
        </div>

        <DeleteConfirmation
            ref="deleteConfirmRef"
            :title="trans('people.delete_confirm_title')"
            :message="trans('people.delete_confirm_message')"
            :acceptLabel="trans('common.delete')"
            :rejectLabel="trans('common.cancel')"
            @confirm="deletePerson"
        />

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <EmptyState
                v-if="people.data.length === 0"
                icon="pi pi-users"
                :title="trans('people.empty')"
                :description="trans('people.empty_description')"
                :actionLabel="trans('people.create')"
                actionRoute="people.create"
            />

            <template v-else>
                <DataTable
                    :value="people.data"
                    :rowHover="true"
                    class="rounded-xl overflow-hidden"
                    tableClass="w-full"
                    stripedRows
                >
                    <Column field="id" :header="trans('common.id')" style="width: 80px" />
                    <Column field="name" :header="trans('people.fields.name')" />
                    <Column :header="trans('people.fields.cpf')">
                        <template #body="{ data }">
                            <span class="font-mono text-sm">{{ formatCpfDisplay(data.cpf) }}</span>
                        </template>
                    </Column>
                    <Column :header="trans('people.fields.gender')">
                        <template #body="{ data }">
                            <Tag
                                :value="trans('genders.' + data.gender)"
                                :severity="genderSeverity[data.gender] || 'secondary'"
                            />
                        </template>
                    </Column>
                    <Column :header="trans('people.fields.birth_date')">
                        <template #body="{ data }">
                            {{ formatDate(data.birth_date) }}
                        </template>
                    </Column>
                    <Column :header="trans('people.fields.phone')">
                        <template #body="{ data }">
                            {{ data.phone ? formatPhoneDisplay(data.phone) : '—' }}
                        </template>
                    </Column>
                    <Column :header="trans('people.fields.email')">
                        <template #body="{ data }">
                            {{ data.email || '—' }}
                        </template>
                    </Column>
                    <Column :header="trans('common.actions')" style="width: 120px">
                        <template #body="{ data }">
                            <div class="flex items-center gap-1">
                                <Button
                                    icon="pi pi-eye"
                                    text
                                    rounded
                                    size="small"
                                    severity="secondary"
                                    :aria-label="trans('common.view')"
                                    :title="trans('common.view')"
                                    @click="router.visit(route('people.edit', data.id))"
                                />
                                <Button
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
                        </template>
                    </Column>
                </DataTable>

                <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ trans('common.showing') }} {{ people.from }} {{ trans('common.to') }} {{ people.to }} {{ trans('common.of') }} {{ people.total }} {{ trans('common.records') }}
                    </span>
                    <Paginator
                        :rows="people.per_page"
                        :totalRecords="people.total"
                        :first="(people.current_page - 1) * people.per_page"
                        template="PrevPageLink PageLinks NextPageLink"
                        class="border-none p-0"
                        @page="onPageChange"
                    />
                </div>
            </template>
        </div>
    </AppLayout>
</template>
