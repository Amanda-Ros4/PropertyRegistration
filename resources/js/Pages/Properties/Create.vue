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
import { formatCpfDisplay } from '@/utils/formatting';

const props = defineProps({
    people: { type: Array, default: () => [] },
});

const peopleOptions = computed(() =>
    props.people.map(p => ({
        value: p.id,
        label: `${p.name} — ${formatCpfDisplay(p.cpf)}`,
    }))
);

const form = useForm({
    person_id: null,
    street: '',
    number: '',
    neighborhood: '',
    complement: '',
});

function submit() {
    form.post(route('properties.store'));
}
</script>

<template>
    <AppLayout :title="trans('properties.create')">
        <Head :title="trans('properties.create')" />

        <PageHeader
            :title="trans('properties.create')"
            backRoute="properties.index"
            :backLabel="trans('common.back')"
        />

        <FormCard>
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
