Exatamente. Aqui está o seu código completo já com as **três alterações** aplicadas:

1. **Ícones de Visibilidade:** O `ref` do Vue e os ícones `Eye`/`EyeOff` foram importados no topo do script para fazer o
botão do olho funcionar.
2. **Limite e Olho:** Os campos de senha agora têm `:maxlength="60"`, o botão flutuante e mudam entre `text` e
`password`.
3. **Layout Ideal:** O "Perfil" foi movido para o lado do "CPF", e o "E-mail" passou a ocupar a linha inteira
(`md:col-span-2`), deixando as duas senhas perfeitamente alinhadas no final.

Pode substituir todo o conteúdo do seu arquivo por este:

```vue
<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Eye, EyeOff } from '@lucide/vue';
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

const showPassword = ref(false);
const showPasswordConfirm = ref(false);

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

        <!-- Container padronizado de largura e centralização -->
        <div class="max-w-5xl mx-auto w-full space-y-6">
            <PageHeader :title="trans('users.create')" backRoute="users.index" :backLabel="trans('common.back')" />

            <FormCard>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <FormField class="md:col-span-2" :label="trans('users.fields.name')" :error="form.errors.name"
                            required>
                            <div class="w-full" @keydown.capture="blockNonLetterNameKey"
                                @beforeinput.capture="blockNonLetterNameBeforeInput">
                                <Input :model-value="form.name" :placeholder="trans('users.placeholders.name')"
                                    :class="cn('w-full', form.errors.name && 'border-destructive')"
                                    @update:model-value="onNameInput" @blur="validateField('name')" />
                            </div>
                        </FormField>

                        <FormField :label="trans('users.fields.cpf')" :error="form.errors.cpf" required>
                            <div class="w-full" @keydown.capture="blockNonDigitKey"
                                @beforeinput.capture="blockNonDigitBeforeInput">
                                <Input :model-value="form.cpf" :placeholder="trans('users.placeholders.cpf')"
                                    :class="cn('w-full', form.errors.cpf && 'border-destructive')" inputmode="numeric"
                                    :maxlength="CPF_INPUT_MAX_LENGTH" @update:model-value="onCpfInput"
                                    @blur="validateField('cpf')" />
                            </div>
                        </FormField>

                        <FormField :label="trans('users.fields.profile')" :error="form.errors.profile" required>
                            <AppSelect v-model="form.profile" :options="profileSelectOptions"
                                :placeholder="trans('users.placeholders.profile')" :invalid="!!form.errors.profile"
                                class="w-full" @change="validateField('profile')" />
                        </FormField>

                        <FormField class="md:col-span-2" :label="trans('users.fields.email')" :error="form.errors.email"
                            required>
                            <Input v-model="form.email" type="email" :placeholder="trans('users.placeholders.email')"
                                :class="cn('w-full', form.errors.email && 'border-destructive')"
                                @blur="validateField('email')" />
                        </FormField>

                        <FormField :label="trans('users.fields.password')" :error="form.errors.password" required>
                            <div class="relative">
                                <Input v-model="form.password" :type="showPassword ? 'text' : 'password'"
                                    :maxlength="60"
                                    :class="cn('w-full pr-10', form.errors.password && 'border-destructive')"
                                    @blur="validateField('password')" />
                                <button type="button" tabindex="-1"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                    @click="showPassword = !showPassword">
                                    <EyeOff v-if="showPassword" class="w-4 h-4" />
                                    <Eye v-else class="w-4 h-4" />
                                </button>
                            </div>
                        </FormField>

                        <FormField :label="trans('users.fields.password_confirmation')"
                            :error="form.errors.password_confirmation" required>
                            <div class="relative">
                                <Input v-model="form.password_confirmation"
                                    :type="showPasswordConfirm ? 'text' : 'password'" :maxlength="60"
                                    :class="cn('w-full pr-10', form.errors.password_confirmation && 'border-destructive')"
                                    @blur="validateField('password_confirmation')" />
                                <button type="button" tabindex="-1"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                    @click="showPasswordConfirm = !showPasswordConfirm">
                                    <EyeOff v-if="showPasswordConfirm" class="w-4 h-4" />
                                    <Eye v-else class="w-4 h-4" />
                                </button>
                            </div>
                        </FormField>

                    </div>

                    <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-gray-100 dark:border-gray-800">
                        <SecondaryButton type="button" @click="router.visit(route('users.index'))">
                            {{ trans('common.cancel') }}
                        </SecondaryButton>
                        <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing || form.validating }"
                            :disabled="form.processing || form.validating">
                            {{ trans('common.save') }}
                        </PrimaryButton>
                    </div>
                </form>
            </FormCard>
        </div>
    </AppLayout>
</template>
