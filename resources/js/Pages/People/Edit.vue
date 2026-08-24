<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Check, Loader2, Trash2 } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import FormCard from '@/Components/FormCard.vue';
import FormField from '@/Components/FormField.vue';
import BirthDateInput from '@/Components/BirthDateInput.vue';
import DeleteConfirmation from '@/Components/DeleteConfirmation.vue';
import AppSelect from '@/Components/AppSelect.vue';
import { Input } from '@/Components/ui/input';
import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';
import {
    blockNonDigitBeforeInput,
    blockNonDigitKey,
    blockNonLetterNameBeforeInput,
    blockNonLetterNameKey,
    formatCpfDisplay,
    formatPersonNameInput,
    formatPhoneInput,
    toBirthDateInputValue,
    stripNonDigits,
    PHONE_BR_INPUT_MAX_LENGTH,
} from '@/utils/formatting';
import { usePrecognitiveForm } from '@/composables/usePrecognitiveForm';
import { useAppToast } from '@/composables/useAppToast';

const { showValidationErrorToast } = useAppToast();

const props = defineProps({
    person: { type: Object, required: true },
    canDelete: { type: Boolean, default: true },
});

const deleteConfirmRef = ref(null);

const genderOptions = computed(() => [
    { value: 'male', label: trans('genders.male') },
    { value: 'female', label: trans('genders.female') },
    { value: 'other', label: trans('genders.other') },
    { value: 'prefer_not_to_say', label: trans('genders.prefer_not_to_say') },
]);

const { form, validateField } = usePrecognitiveForm('put', route('people.update', props.person.id), {
    name: props.person.name,
    birth_date: toBirthDateInputValue(props.person.birth_date),
    gender: props.person.gender?.value ?? props.person.gender,
    phone: formatPhoneInput(props.person.phone ?? ''),
    email: props.person.email ?? '',
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

function onPhoneInput(value) {
    syncMaskedField('phone', formatPhoneInput, value);
    if (stripNonDigits(form.phone).length === 11) {
        validateField('phone');
    }
}

function onBirthDateInput(value) {
    if (stripNonDigits(value).length === 8) {
        validateField('birth_date');
    }
}

function submit() {
    form.submit({
        onError: showValidationErrorToast,
    });
}

function openDeleteConfirm() {
    deleteConfirmRef.value?.open();
}

function deletePerson() {
    router.delete(route('people.destroy', props.person.id), {
        onFinish: () => {
            deleteConfirmRef.value = null;
        },
    });
}
</script>

<template>
    <AppLayout :title="trans('people.edit')">
        <Head :title="trans('people.edit')" />

        <PageHeader :title="trans('people.edit')" backRoute="people.index" :backLabel="trans('common.back')" />

        <FormCard>
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <FormField class="md:col-span-2" :label="trans('people.fields.name')" :error="form.errors.name" required>
                        <div
                            class="w-full"
                            @keydown.capture="blockNonLetterNameKey"
                            @beforeinput.capture="blockNonLetterNameBeforeInput"
                        >
                            <Input
                                :model-value="form.name"
                                :placeholder="trans('people.placeholders.name')"
                                :class="cn('w-full', form.errors.name && 'border-destructive')"
                                @update:model-value="onNameInput"
                                @blur="validateField('name')"
                            />
                        </div>
                    </FormField>

                    <FormField :label="trans('people.fields.cpf')" required>
                        <Input
                            :model-value="formatCpfDisplay(person.cpf)"
                            :placeholder="trans('people.placeholders.cpf')"
                            class="w-full"
                            disabled
                        />
                    </FormField>

                    <FormField :label="trans('people.fields.gender')" :error="form.errors.gender" required>
                        <AppSelect
                            v-model="form.gender"
                            :options="genderOptions"
                            :placeholder="trans('people.placeholders.gender')"
                            :invalid="!!form.errors.gender"
                            class="w-full"
                            @change="validateField('gender')"
                        />
                    </FormField>

                    <FormField :label="trans('people.fields.birth_date')" :error="form.errors.birth_date" required>
                        <BirthDateInput
                            v-model="form.birth_date"
                            :placeholder="trans('people.placeholders.birth_date')"
                            :invalid="!!form.errors.birth_date"
                            @update:model-value="onBirthDateInput"
                            @blur="validateField('birth_date')"
                        />
                    </FormField>

                    <FormField :label="trans('people.fields.phone')" :error="form.errors.phone">
                        <div
                            class="w-full"
                            @keydown.capture="blockNonDigitKey"
                            @beforeinput.capture="blockNonDigitBeforeInput"
                        >
                            <Input
                                :model-value="form.phone"
                                :placeholder="trans('people.placeholders.phone')"
                                :class="cn('w-full', form.errors.phone && 'border-destructive')"
                                inputmode="numeric"
                                :maxlength="PHONE_BR_INPUT_MAX_LENGTH"
                                @update:model-value="onPhoneInput"
                                @blur="validateField('phone')"
                            />
                        </div>
                    </FormField>

                    <FormField :label="trans('people.fields.email')" :error="form.errors.email">
                        <Input
                            v-model="form.email"
                            type="email"
                            :placeholder="trans('people.placeholders.email')"
                            :class="cn('w-full', form.errors.email && 'border-destructive')"
                            @blur="validateField('email')"
                        />
                    </FormField>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <Button
                        v-if="canDelete"
                        type="button"
                        variant="outline"
                        class="text-destructive border-destructive/50 hover:bg-destructive/10"
                        @click="openDeleteConfirm"
                    >
                        <Trash2 class="size-4" />
                        {{ trans('common.delete') }}
                    </Button>

                    <div class="flex justify-end gap-3" :class="{ 'sm:ml-auto': canDelete }">
                        <Button
                            type="button"
                            variant="outline"
                            @click="router.visit(route('people.index'))"
                        >
                            {{ trans('common.cancel') }}
                        </Button>
                        <Button
                            type="submit"
                            :disabled="form.processing || form.validating"
                        >
                            <Loader2 v-if="form.processing || form.validating" class="size-4 animate-spin" />
                            <Check v-else class="size-4" />
                            {{ trans('common.save') }}
                        </Button>
                    </div>
                </div>
            </form>
        </FormCard>

        <DeleteConfirmation
            v-if="canDelete"
            ref="deleteConfirmRef"
            :title="trans('people.delete_confirm_title')"
            :message="trans('people.delete_confirm_message_detail', { name: person.name })"
            @confirm="deletePerson"
        />
    </AppLayout>
</template>
