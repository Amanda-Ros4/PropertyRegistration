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
import DatePicker from 'primevue/datepicker';
import Button from 'primevue/button';

const props = defineProps({
    person: { type: Object, required: true },
});

const genderOptions = computed(() => [
    { value: 'male', label: trans('genders.male') },
    { value: 'female', label: trans('genders.female') },
    { value: 'other', label: trans('genders.other') },
    { value: 'prefer_not_to_say', label: trans('genders.prefer_not_to_say') },
]);

function parseBirthDate(dateStr) {
    if (!dateStr) return null;
    const [y, m, d] = dateStr.split('-');
    return new Date(Number(y), Number(m) - 1, Number(d));
}

const form = useForm({
    name: props.person.name,
    birth_date: parseBirthDate(props.person.birth_date),
    cpf: props.person.cpf,
    gender: props.person.gender,
    phone: props.person.phone ?? '',
    email: props.person.email ?? '',
});

function formatCpf(value) {
    const digits = value.replace(/\D/g, '').slice(0, 11);
    let formatted = digits;
    if (digits.length > 3) formatted = digits.slice(0, 3) + '.' + digits.slice(3);
    if (digits.length > 6) formatted = formatted.slice(0, 7) + '.' + digits.slice(6);
    if (digits.length > 9) formatted = formatted.slice(0, 11) + '-' + digits.slice(9);
    form.cpf = formatted;
}

function formatPhone(value) {
    const digits = value.replace(/\D/g, '').slice(0, 11);
    let formatted = digits;
    if (digits.length > 0) formatted = '(' + digits.slice(0, 2);
    if (digits.length > 2) formatted += ') ' + digits.slice(2, 7);
    if (digits.length > 7) formatted += '-' + digits.slice(7);
    form.phone = formatted;
}

function formatDateForSubmit(date) {
    if (!date) return null;
    if (typeof date === 'string') return date;
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function submit() {
    form
        .transform(data => ({
            ...data,
            birth_date: formatDateForSubmit(data.birth_date),
        }))
        .put(route('people.update', props.person.id));
}
</script>

<template>
    <AppLayout :title="trans('people.edit')">
        <Head :title="trans('people.edit')" />

        <PageHeader
            :title="trans('people.edit')"
            backRoute="people.index"
            :backLabel="trans('common.back')"
        />

        <FormCard>
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <FormField
                        class="md:col-span-2"
                        :label="trans('people.fields.name')"
                        :error="form.errors.name"
                        required
                    >
                        <InputText
                            v-model="form.name"
                            :placeholder="trans('people.placeholders.name')"
                            :invalid="!!form.errors.name"
                            class="w-full"
                        />
                    </FormField>

                    <!-- CPF -->
                    <FormField
                        :label="trans('people.fields.cpf')"
                        :error="form.errors.cpf"
                        required
                    >
                        <InputText
                            :modelValue="form.cpf"
                            :placeholder="trans('people.placeholders.cpf')"
                            :invalid="!!form.errors.cpf"
                            class="w-full font-mono"
                            @input="formatCpf($event.target.value)"
                        />
                    </FormField>

                    <!-- Gender -->
                    <FormField
                        :label="trans('people.fields.gender')"
                        :error="form.errors.gender"
                        required
                    >
                        <Select
                            v-model="form.gender"
                            :options="genderOptions"
                            optionLabel="label"
                            optionValue="value"
                            :placeholder="trans('people.placeholders.gender')"
                            :invalid="!!form.errors.gender"
                            class="w-full"
                        />
                    </FormField>

                    <!-- Birth Date -->
                    <FormField
                        :label="trans('people.fields.birth_date')"
                        :error="form.errors.birth_date"
                        required
                    >
                        <DatePicker
                            v-model="form.birth_date"
                            :placeholder="trans('people.placeholders.birth_date')"
                            :invalid="!!form.errors.birth_date"
                            :maxDate="new Date()"
                            showIcon
                            dateFormat="dd/mm/yy"
                            class="w-full"
                        />
                    </FormField>

                    <!-- Phone -->
                    <FormField
                        :label="trans('people.fields.phone')"
                        :error="form.errors.phone"
                    >
                        <InputText
                            :modelValue="form.phone"
                            :placeholder="trans('people.placeholders.phone')"
                            :invalid="!!form.errors.phone"
                            class="w-full"
                            @input="formatPhone($event.target.value)"
                        />
                    </FormField>

                    <!-- Email -->
                    <FormField
                        :label="trans('people.fields.email')"
                        :error="form.errors.email"
                    >
                        <InputText
                            v-model="form.email"
                            type="email"
                            :placeholder="trans('people.placeholders.email')"
                            :invalid="!!form.errors.email"
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
                        @click="router.visit(route('people.index'))"
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
