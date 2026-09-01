<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Check, Loader2 } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import FormCard from '@/Components/FormCard.vue';
import FormField from '@/Components/FormField.vue';
import AppSelect from '@/Components/AppSelect.vue';
import { Input } from '@/Components/ui/input';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { cn } from '@/lib/utils';
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

const passwordRules = computed(() => {
    const pwd = form.password || '';
    return {
        length: pwd.length >= 8 && pwd.length <= 128,
        mixed: /[a-z]/.test(pwd) && /[A-Z]/.test(pwd),
        number: /\d/.test(pwd),
        symbol: /[!@#$%^&*(),.?":{}|<>]/.test(pwd),
    };
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
                            <Input
                                :model-value="form.name"
                                :placeholder="trans('users.placeholders.name')"
                                :class="cn('w-full', form.errors.name && 'border-destructive')"
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
                            <Input
                                :model-value="form.cpf"
                                :placeholder="trans('users.placeholders.cpf')"
                                :class="cn('w-full', form.errors.cpf && 'border-destructive')"
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
                        <Input
                            v-model="form.email"
                            type="email"
                            :placeholder="trans('users.placeholders.email')"
                            :class="cn('w-full', form.errors.email && 'border-destructive')"
                            @blur="validateField('email')"
                        />
                    </FormField>

                    <FormField
                        :label="trans('users.fields.profile')"
                        :error="form.errors.profile"
                        required
                    >
                        <AppSelect
                            v-model="form.profile"
                            :options="profileSelectOptions"
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
                        <Input
                            v-model="form.password"
                            type="password"
                            maxlength="128"
                            :class="cn('w-full', form.errors.password && 'border-destructive')"
                            @blur="validateField('password')"
                        />

                        <ul class="mt-2 space-y-1 text-xs">
                            <li :class="passwordRules.length ? 'text-green-600 dark:text-green-400 font-medium' : 'text-muted-foreground'">
                                {{ passwordRules.length ? '✓' : '•' }} Mínimo de 8 e máximo de 128 caracteres
                            </li>
                            <li :class="passwordRules.mixed ? 'text-green-600 dark:text-green-400 font-medium' : 'text-muted-foreground'">
                                {{ passwordRules.mixed ? '✓' : '•' }} Letras maiúsculas e minúsculas
                            </li>
                            <li :class="passwordRules.number ? 'text-green-600 dark:text-green-400 font-medium' : 'text-muted-foreground'">
                                {{ passwordRules.number ? '✓' : '•' }} Pelo menos um número
                            </li>
                            <li :class="passwordRules.symbol ? 'text-green-600 dark:text-green-400 font-medium' : 'text-muted-foreground'">
                                {{ passwordRules.symbol ? '✓' : '•' }} Pelo menos um caractere especial (!@#$%...)
                            </li>
                        </ul>
                    </FormField>

                    <FormField
                        :label="trans('users.fields.password_confirmation')"
                        :error="form.errors.password_confirmation"
                        required
                    >
                        <Input
                            v-model="form.password_confirmation"
                            type="password"
                            maxlength="128"
                            :class="cn('w-full', form.errors.password_confirmation && 'border-destructive')"
                            @blur="validateField('password_confirmation')"
                        />
                    </FormField>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <SecondaryButton
                        type="button"
                        @click="router.visit(route('users.index'))"
                    >
                        {{ trans('common.cancel') }}
                    </SecondaryButton>
                    <PrimaryButton
                        type="submit"
                        :class="{ 'opacity-25': form.processing || form.validating }"
                        :disabled="form.processing || form.validating"
                    >
                        {{ trans('common.save') }}
                    </PrimaryButton>
                </div>
            </form>
        </FormCard>
    </AppLayout>
</template>