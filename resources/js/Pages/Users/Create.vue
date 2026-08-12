<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import FormCard from '@/Components/FormCard.vue';
import FormField from '@/Components/FormField.vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Select from 'primevue/select';
import Button from 'primevue/button';
import {
    CPF_INPUT_MAX_LENGTH,
    blockNonDigitBeforeInput,
    blockNonDigitKey,
    blockNonLetterNameBeforeInput,
    blockNonLetterNameKey,
    formatCpfInput,
    formatPersonNameInput,
    stripNonDigits,
} from '@/utils/formatting';
import { usePrecognitiveForm } from '@/composables/usePrecognitiveForm';
import { useAppToast } from '@/composables/useAppToast';

const props = defineProps({
    profileOptions: { type: Array, default: () => [] },
});

const { showValidationErrorToast } = useAppToast();

const profileSelectOptions = computed(() =>
    props.profileOptions.map((option) => ({
        value: option.value,
        label: trans(option.label_key),
    })),
);

const { form, validateField } = usePrecognitiveForm('post', route('users.store'), {
    name: '',
    email: '',
    cpf: '',
    password: '',
    password_confirmation: '',
    profile: profileSelectOptions.value[0]?.value ?? 'A',
});

function syncMaskedField(field, formatter, value) {
    const formatted = formatter(value);
    if (formatted === form[field]) {
        form[field] = `${formatted}\u200b`;
        queueMicrotask(() => {
            form[field] = formatted;
        });
        return;
    }
    form[field] = formatted;
}

function onNameInput(value) {
    syncMaskedField('name', formatPersonNameInput, value);
}

function onCpfInput(value) {
    syncMaskedField('cpf', formatCpfInput, value);
    if (stripNonDigits(form.cpf).length === 11) {
        validateField('cpf');
    }
}

function submit() {
    form.submit({
        onError: showValidationErrorToast,
    });
}
</script>

<template>
    <AppLayout :title="trans('users.create')">
        <Head :title="trans('users.create')" />

        <PageHeader
            :title="trans('users.create')"
            backRoute="users.index"
            :backLabel="trans('common.back')"
        />

        <FormCard>
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <FormField
                        class="md:col-span-2"
                        :label="trans('users.fields.name')"
                        :error="form.errors.name"
                        required
                    >
                        <div
                            class="w-full"
                            @keydown.capture="blockNonLetterNameKey"
                            @beforeinput.capture="blockNonLetterNameBeforeInput"
                        >
                            <InputText
                                :modelValue="form.name"
                                :placeholder="trans('users.placeholders.name')"
                                :invalid="!!form.errors.name"
                                class="w-full"
                                @update:model-value="onNameInput"
                                @blur="validateField('name')"
                            />
                        </div>
                    </FormField>

                    <FormField
                        :label="trans('users.fields.cpf')"
                        :error="form.errors.cpf"
                        required
                    >
                        <div
                            class="w-full"
                            @keydown.capture="blockNonDigitKey"
                            @beforeinput.capture="blockNonDigitBeforeInput"
                        >
                            <InputText
                                :modelValue="form.cpf"
                                :placeholder="trans('users.placeholders.cpf')"
                                :invalid="!!form.errors.cpf"
                                class="w-full font-mono"
                                inputmode="numeric"
                                :maxlength="CPF_INPUT_MAX_LENGTH"
                                @update:model-value="onCpfInput"
                                @blur="validateField('cpf')"
                            />
                        </div>
                    </FormField>

                    <FormField
                        :label="trans('users.fields.email')"
                        :error="form.errors.email"
                        required
                    >
                        <InputText
                            v-model="form.email"
                            type="email"
                            :placeholder="trans('users.placeholders.email')"
                            :invalid="!!form.errors.email"
                            class="w-full"
                            @blur="validateField('email')"
                        />
                    </FormField>

                    <FormField
                        :label="trans('users.fields.profile')"
                        :error="form.errors.profile"
                        required
                    >
                        <Select
                            v-model="form.profile"
                            :options="profileSelectOptions"
                            optionLabel="label"
                            optionValue="value"
                            :placeholder="trans('users.placeholders.profile')"
                            :invalid="!!form.errors.profile"
                            class="w-full"
                            @change="validateField('profile')"
                        />
                    </FormField>

                    <FormField
                        :label="trans('users.fields.password')"
                        :error="form.errors.password"
                        required
                    >
                        <Password
                            v-model="form.password"
                            :invalid="!!form.errors.password"
                            toggleMask
                            :feedback="false"
                            class="w-full"
                            inputClass="w-full"
                            @blur="validateField('password')"
                        />
                    </FormField>

                    <FormField
                        :label="trans('users.fields.password_confirmation')"
                        :error="form.errors.password_confirmation"
                        required
                    >
                        <Password
                            v-model="form.password_confirmation"
                            :invalid="!!form.errors.password_confirmation"
                            toggleMask
                            :feedback="false"
                            class="w-full"
                            inputClass="w-full"
                            @blur="validateField('password_confirmation')"
                        />
                    </FormField>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <Button
                        type="button"
                        :label="trans('common.cancel')"
                        severity="secondary"
                        outlined
                        @click="router.visit(route('users.index'))"
                    />
                    <Button
                        type="submit"
                        :label="trans('common.save')"
                        icon="pi pi-check"
                        :loading="form.processing || form.validating"
                    />
                </div>
            </form>
        </FormCard>
    </AppLayout>
</template>
