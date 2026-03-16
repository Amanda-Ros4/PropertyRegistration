<script setup>
import { computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import FormCard from '@/Components/FormCard.vue';
import FormField from '@/Components/FormField.vue';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

const props = defineProps({
    property: { type: Object, required: true },
    people: { type: Array, default: () => [] },
});

function formatCpf(cpf) {
    const digits = cpf?.replace(/\D/g, '') ?? '';
    if (digits.length === 11) {
        return `${digits.slice(0,3)}.${digits.slice(3,6)}.${digits.slice(6,9)}-${digits.slice(9)}`;
    }
    return cpf;
}

const peopleOptions = computed(() =>
    props.people.map(p => ({
        value: p.id,
        label: `${p.name} — ${formatCpf(p.cpf)}`,
    }))
);

const form = useForm({
    person_id: props.property.person_id,
    street: props.property.street,
    number: props.property.number,
    neighborhood: props.property.neighborhood,
    complement: props.property.complement ?? '',
});

function submit() {
    form.put(route('properties.update', props.property.id));
}
</script>

<template>
    <AppLayout :title="trans('properties.edit')">
        <Head :title="trans('properties.edit')" />

        <PageHeader
            :title="trans('properties.edit')"
            backRoute="properties.index"
            :backLabel="trans('common.back')"
        />

        <FormCard>
            <!-- Municipal Registration display -->
            <div class="flex items-center gap-3 p-4 bg-indigo-50 dark:bg-indigo-950/30 rounded-lg border border-indigo-100 dark:border-indigo-900 mb-6">
                <i class="pi pi-hashtag text-indigo-500" />
                <div>
                    <p class="text-xs text-indigo-500 dark:text-indigo-400 font-medium uppercase tracking-wide">
                        {{ trans('properties.fields.municipal_registration') }}
                    </p>
                    <p class="text-xl font-bold text-indigo-700 dark:text-indigo-300 font-mono">#{{ property.id }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Owner -->
                    <FormField
                        class="md:col-span-2"
                        :label="trans('properties.fields.owner')"
                        :error="form.errors.person_id"
                        required
                    >
                        <Select
                            v-model="form.person_id"
                            :options="peopleOptions"
                            optionLabel="label"
                            optionValue="value"
                            :placeholder="trans('properties.placeholders.owner')"
                            :invalid="!!form.errors.person_id"
                            filter
                            class="w-full"
                        />
                    </FormField>

                    <!-- Street -->
                    <FormField
                        class="md:col-span-2"
                        :label="trans('properties.fields.street')"
                        :error="form.errors.street"
                        required
                    >
                        <InputText
                            v-model="form.street"
                            :placeholder="trans('properties.placeholders.street')"
                            :invalid="!!form.errors.street"
                            class="w-full"
                        />
                    </FormField>

                    <!-- Number -->
                    <FormField
                        :label="trans('properties.fields.number')"
                        :error="form.errors.number"
                        required
                    >
                        <InputText
                            v-model="form.number"
                            :placeholder="trans('properties.placeholders.number')"
                            :invalid="!!form.errors.number"
                            class="w-full"
                        />
                    </FormField>

                    <!-- Neighborhood -->
                    <FormField
                        :label="trans('properties.fields.neighborhood')"
                        :error="form.errors.neighborhood"
                        required
                    >
                        <InputText
                            v-model="form.neighborhood"
                            :placeholder="trans('properties.placeholders.neighborhood')"
                            :invalid="!!form.errors.neighborhood"
                            class="w-full"
                        />
                    </FormField>

                    <!-- Complement -->
                    <FormField
                        class="md:col-span-2"
                        :label="trans('properties.fields.complement')"
                        :error="form.errors.complement"
                    >
                        <InputText
                            v-model="form.complement"
                            :placeholder="trans('properties.placeholders.complement')"
                            :invalid="!!form.errors.complement"
                            class="w-full"
                        />
                    </FormField>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <Button
                        type="button"
                        :label="trans('common.cancel')"
                        severity="secondary"
                        outlined
                        @click="router.visit(route('properties.index'))"
                    />
                    <Button
                        type="submit"
                        :label="trans('common.save')"
                        icon="pi pi-check"
                        :loading="form.processing"
                    />
                </div>
            </form>
        </FormCard>
    </AppLayout>
</template>
