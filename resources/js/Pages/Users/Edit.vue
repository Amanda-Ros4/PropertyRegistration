<script setup>
import { computed, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Ban, Check, CheckCircle, Loader2 } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import FormCard from '@/Components/FormCard.vue';
import FormField from '@/Components/FormField.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import AppSelect from '@/Components/AppSelect.vue';
import { Input } from '@/Components/ui/input';
import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';
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
    canChangeProfile: { type: Boolean, default: false },
    canEditEmail: { type: Boolean, default: false },
    isSelf: { type: Boolean, default: false },
});

const page = usePage();

const isTiAdmin = computed(() =>
    Boolean(page.props.permissions?.isTiAdmin ?? props.canEditEmail),
);

const canEditUserEmail = computed(() => isTiAdmin.value && props.canUpdate);

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

const activeValue = computed(() => props.user.active);
const activeLabel = computed(() =>
    trans(activeValue.value === 'S' ? 'users.active_status.active' : 'users.active_status.inactive'),
);
const activeSeverity = computed(() =>
    activeValue.value === 'S' ? 'success' : 'secondary',
);

const activeUpdating = ref(false);
const nextActive = computed(() => (activeValue.value === 'S' ? 'N' : 'S'));
const activeActionLabel = computed(() =>
    activeValue.value === 'S'
        ? trans('users.actions.deactivate')
        : trans('users.actions.activate'),
);

function toggleActive() {
    if (activeUpdating.value || !props.canUpdate || props.isSelf) {
        return;
    }

    activeUpdating.value = true;
    router.patch(
        route('users.active.update', props.user.id),
        { active: nextActive.value },
        {
            preserveScroll: true,
            onFinish: () => {
                activeUpdating.value = false;
            },
        },
    );
}

const profileLabelKey = {
    T: 'users.profiles.ti_admin',
    S: 'users.profiles.system_admin',
    A: 'users.profiles.attendant',
};

const { form, validateField } = usePrecognitiveForm('put', route('users.update', props.user.id), {
    name: props.user.name,
    ...(page.props.permissions?.isTiAdmin || props.canEditEmail ? { email: props.user.email } : {}),
    password: '',
    password_confirmation: '',
    profile: props.user.profile,
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
            <div
                class="mb-6 p-4 bg-slate-50 dark:bg-slate-900/40 rounded-lg border border-slate-200 dark:border-slate-800"
            >
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wide mb-2">
                    {{ trans('users.fields.active') }}
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <StatusBadge :value="activeLabel" :severity="activeSeverity" />
                    <Button
                        v-if="canUpdate && !isSelf"
                        type="button"
                        variant="outline"
                        size="sm"
                        :disabled="activeUpdating"
                        @click="toggleActive"
                    >
                        <Loader2 v-if="activeUpdating" class="size-4 animate-spin" />
                        <Ban v-else-if="activeValue === 'S'" class="size-4" />
                        <CheckCircle v-else class="size-4" />
                        {{ activeActionLabel }}
                    </Button>
                </div>
                <p class="text-xs text-slate-400 mt-2">
                    {{
                        isSelf
                            ? trans('users.hint_cannot_deactivate_self')
                            : canUpdate
                              ? trans('users.hint_active_toggle')
                              : trans('users.hint_active_readonly')
                    }}
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <FormField
                        :label="trans('common.id')"
                        class="md:col-span-2"
                    >
                        <Input
                            :model-value="String(user.id)"
                            class="w-full max-w-xs"
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
                            <Input
                                :model-value="form.name"
                                :placeholder="trans('users.placeholders.name')"
                                :class="cn('w-full', form.errors.name && 'border-destructive')"
                                :disabled="!canUpdate"
                                @update:model-value="onNameInput"
                                @blur="canUpdate ? validateField('name') : null"
                            />
                        </div>
                    </FormField>

                    <FormField
                        :label="trans('users.fields.cpf')"
                    >
                        <Input
                            :model-value="formatCpfDisplay(user.cpf)"
                            class="w-full"
                            disabled
                            readonly
                        />
                    </FormField>

                    <FormField
                        :label="trans('users.fields.email')"
                        :error="form.errors.email"
                        :required="canEditUserEmail"
                    >
                        <Input
                            v-if="canEditUserEmail"
                            v-model="form.email"
                            type="email"
                            :placeholder="trans('users.placeholders.email')"
                            :class="cn('w-full', form.errors.email && 'border-destructive')"
                            @blur="validateField('email')"
                        />
                        <Input
                            v-else
                            :model-value="user.email"
                            type="email"
                            class="w-full"
                            disabled
                            readonly
                        />
                    </FormField>

                    <FormField
                        :label="trans('users.fields.profile')"
                        :error="form.errors.profile"
                        :required="canChangeProfile"
                        :hint="isSelf && canUpdate && !canChangeProfile ? trans('users.hint_profile_self_readonly') : null"
                    >
                        <AppSelect
                            v-if="canChangeProfile"
                            v-model="form.profile"
                            :options="profileSelectOptions"
                            :placeholder="trans('users.placeholders.profile')"
                            :invalid="!!form.errors.profile"
                            class="w-full"
                            @change="validateField('profile')"
                        />
                        <Input
                            v-else
                            :model-value="trans(profileLabelKey[user.profile] || 'users.profiles.attendant')"
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
                        <Input
                            v-model="form.password"
                            type="password"
                            :placeholder="trans('users.placeholders.password_optional')"
                            :class="cn('w-full', form.errors.password && 'border-destructive')"
                            @blur="validateField('password')"
                        />
                    </FormField>

                    <FormField
                        v-if="canUpdate"
                        class="md:col-span-2"
                        :label="trans('users.fields.password_confirmation')"
                        :error="form.errors.password_confirmation"
                    >
                        <Input
                            v-model="form.password_confirmation"
                            type="password"
                            :class="cn('w-full', form.errors.password_confirmation && 'border-destructive')"
                            @blur="validateField('password_confirmation')"
                        />
                    </FormField>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <Button
                        type="button"
                        variant="outline"
                        @click="router.visit(route('users.index'))"
                    >
                        {{ trans('common.back') }}
                    </Button>
                    <Button
                        v-if="canUpdate"
                        type="submit"
                        :disabled="form.processing || form.validating"
                    >
                        <Loader2 v-if="form.processing || form.validating" class="size-4 animate-spin" />
                        <Check v-else class="size-4" />
                        {{ trans('common.save') }}
                    </Button>
                </div>
            </form>
        </FormCard>
    </AppLayout>
</template>
