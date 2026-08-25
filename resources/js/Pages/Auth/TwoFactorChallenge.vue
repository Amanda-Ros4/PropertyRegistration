<script setup>
import { nextTick, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { AUTH_INTRO_CLASS, AUTH_LINK_CLASS } from '@/lib/auth-ui';
import {
    AUTHENTICATOR_CODE_LENGTH,
    blockNonDigitBeforeInput,
    blockNonDigitKey,
    stripNonDigits,
} from '@/utils/formatting';

const recovery = ref(false);
const recoveryCodeInput = ref(null);
const codeInput = ref(null);

const form = useForm({
    code: '',
    recovery_code: '',
});

function onCodeInput(value) {
    form.code = stripNonDigits(value, AUTHENTICATOR_CODE_LENGTH);
}

async function toggleRecovery() {
    recovery.value = !recovery.value;
    await nextTick();

    if (recovery.value) {
        form.code = '';
        recoveryCodeInput.value?.focus();
        return;
    }

    form.recovery_code = '';
    codeInput.value?.focus();
}

function submit() {
    if (!recovery.value) {
        form.code = stripNonDigits(form.code, AUTHENTICATOR_CODE_LENGTH);
    }

    form.post(route('two-factor.login'));
}
</script>

<template>
    <Head :title="trans('auth.two_factor.title')" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4">
            <p class="font-semibold text-sm text-gray-700 dark:text-gray-100 mb-2">
                {{ trans('auth.two_factor.title') }}
            </p>
            <p :class="AUTH_INTRO_CLASS">
                {{ recovery ? trans('auth.two_factor.recovery_prompt') : trans('auth.two_factor.code_prompt') }}
            </p>
        </div>

        <form @submit.prevent="submit">
            <div v-if="!recovery">
                <InputLabel for="code" :value="trans('auth.two_factor.code')" />
                <div
                    @keydown.capture="blockNonDigitKey"
                    @beforeinput.capture="blockNonDigitBeforeInput"
                >
                    <TextInput
                        id="code"
                        ref="codeInput"
                        :model-value="form.code"
                        type="text"
                        inputmode="numeric"
                        :maxlength="AUTHENTICATOR_CODE_LENGTH"
                        class="mt-1 block w-full tracking-widest"
                        autofocus
                        autocomplete="one-time-code"
                        @update:model-value="onCodeInput"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div v-else>
                <InputLabel for="recovery_code" :value="trans('auth.two_factor.recovery_code')" />
                <TextInput
                    id="recovery_code"
                    ref="recoveryCodeInput"
                    v-model="form.recovery_code"
                    type="text"
                    class="mt-1 block w-full"
                    autocomplete="one-time-code"
                />
                <InputError class="mt-2" :message="form.errors.recovery_code" />
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mt-4">
                <button
                    type="button"
                    :class="AUTH_LINK_CLASS"
                    class="text-left bg-transparent border-0 p-0 cursor-pointer"
                    @click="toggleRecovery"
                >
                    {{ recovery ? trans('auth.two_factor.use_code') : trans('auth.two_factor.use_recovery') }}
                </button>
            </div>

            <div class="mt-6">
                <PrimaryButton
                    type="submit"
                    class="w-full"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ trans('auth.login') }}
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
