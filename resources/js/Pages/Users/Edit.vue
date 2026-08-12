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
    blockNonLetterNameBeforeInput,
    blockNonLetterNameKey,
    formatCpfDisplay,
    formatPersonNameInput,
} from '@/utils/formatting';
import { usePrecognitiveForm } from '@/composables/usePrecognitiveForm';
import { useAppToast } from '@/composables/useAppToast';

const props = defineProps({
    user: { type: Object, required: true },
    profileOptions: { type: Array, default: () => [] },
    canUpdate: { type: Boolean, default: false },
});

const { showValidationErrorToast } = useAppToast();

const pageTitle = computed(() =>
    props.canUpdate ? trans('users.edit') : trans('users.view'),
);

const profileSelectOptions = computed(() =>
    props.profileOptions.map((option) => ({
        value: option.value,
        label: trans(option.label_key),
    })),
);

const activeOptions = computed(() => [
    { value: 'S', label: trans('users.active_status.active') },
    { value: 'N', label: trans('users.active_status.inactive') },
]);

const profileLabelKey = {
    T: 'users.profiles.ti_admin',
    S: 'users.profiles.system_admin',
    A: 'users.profiles.attendant',
};

const { form, validateField } = usePrecognitiveForm('put', route('users.update', props.user.id), {
    name: props.user.name,
    password: '',
    password_confirmation: '',
    profile: props.user.profile,
    active: props.user.active,
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
    if (!props.canUpdate) {
        return;
    }
    syncMaskedField('name', formatPersonNameInput, value);
}

function submit() {
    if (!props.canUpdate) {
        return;
    }

    form.submit({
        onError: showValidationErrorToast,
    });
}
</script>

<template>
    <AppLayout :title="pageTitle">
        <Head :title="pageTitle" />

        <PageHeader
            :title="pageTitle"
            backRoute="users.index"
            :backLabel="trans('common.back')"
        />

        <FormCard>
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <FormField
                        :label="trans('common.id')"
                        class="md:col-span-2"
                    >
                        <InputText
                            :modelValue="String(user.id)"
                            class="w-full max-w-xs font-mono"
                            disabled
                            readonly
                        />
                    </FormField>

                    <FormField
                        class="md:col-span-2"
                        :label="trans('users.fields.name')"
                        :error="form.errors.name"
                        :required="canUpdate"
                    >
                        <div
                            class="w-full"
                            @keydown.capture="canUpdate ? blockNonLetterNameKey : null"
                            @beforeinput.capture="canUpdate ? blockNonLetterNameBeforeInput : null"
                        >
                            <InputText
                                :modelValue="form.name"
                                :placeholder="trans('users.placeholders.name')"
                                :invalid="!!form.errors.name"
                                class="w-full"
                                :disabled="!canUpdate"
                                @update:model-value="onNameInput"
                                @blur="canUpdate ? validateField('name') : null"
                            />
                        </div>
                    </FormField>

                    <FormField
                        :label="trans('users.fields.cpf')"
                    >
                        <InputText
                            :modelValue="formatCpfDisplay(user.cpf)"
                            class="w-full font-mono"
                            disabled
                            readonly
                        />
                    </FormField>

                    <FormField
                        :label="trans('users.fields.email')"
                    >
                        <InputText
                            :modelValue="user.email"
                            type="email"
                            class="w-full"
                            disabled
                            readonly
                        />
                    </FormField>

                    <FormField
                        :label="trans('users.fields.profile')"
                        :error="form.errors.profile"
                        :required="canUpdate"
                    >
                        <Select
                            v-if="canUpdate"
                            v-model="form.profile"
                            :options="profileSelectOptions"
                            optionLabel="label"
                            optionValue="value"
                            :placeholder="trans('users.placeholders.profile')"
                            :invalid="!!form.errors.profile"
                            class="w-full"
                            @change="validateField('profile')"
                        />
                        <InputText
                            v-else
                            :modelValue="trans(profileLabelKey[user.profile] || 'users.profiles.attendant')"
                            class="w-full"
                            disabled
                            readonly
                        />
                    </FormField>

                    <FormField
                        :label="trans('users.fields.active')"
                        :error="form.errors.active"
                        :required="canUpdate"
                    >
                        <Select
                            v-if="canUpdate"
                            v-model="form.active"
                            :options="activeOptions"
                            optionLabel="label"
                            optionValue="value"
                            :placeholder="trans('users.placeholders.active')"
                            :invalid="!!form.errors.active"
                            class="w-full"
                            @change="validateField('active')"
                        />
                        <InputText
                            v-else
                            :modelValue="trans(user.active === 'S' ? 'users.active_status.active' : 'users.active_status.inactive')"
                            class="w-full"
                            disabled
                            readonly
                        />
                    </FormField>

                    <FormField
                        v-if="canUpdate"
                        class="md:col-span-2"
                        :label="trans('users.fields.password')"
                        :hint="trans('users.password_optional_hint')"
                        :error="form.errors.password"
                    >
                        <Password
                            v-model="form.password"
                            :invalid="!!form.errors.password"
                            toggleMask
                            :feedback="false"
                            class="w-full"
                            inputClass="w-full"
                            :placeholder="trans('users.placeholders.password_optional')"
                            @blur="validateField('password')"
                        />
                    </FormField>

                    <FormField
                        v-if="canUpdate"
                        class="md:col-span-2"
                        :label="trans('users.fields.password_confirmation')"
                        :error="form.errors.password_confirmation"
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
                        :label="trans('common.back')"
                        severity="secondary"
                        outlined
                        @click="router.visit(route('users.index'))"
                    />
                    <Button
                        v-if="canUpdate"
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
