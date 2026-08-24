<script setup>
import { nextTick, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Loader2, LogIn } from '@lucide/vue';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Button } from '@/Components/ui/button';
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

        <h1 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">
            {{ trans('auth.two_factor.title') }}
        </h1>

        <p class="mb-4 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
            {{ recovery ? trans('auth.two_factor.recovery_prompt') : trans('auth.two_factor.code_prompt') }}
        </p>

        <form @submit.prevent="submit" class="space-y-4">
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

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                <button
                    type="button"
                    class="text-sm text-green-700 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 underline-offset-2 hover:underline text-left"
                    @click="toggleRecovery"
                >
                    {{ recovery ? trans('auth.two_factor.use_code') : trans('auth.two_factor.use_recovery') }}
                </button>

                <Button
                    type="submit"
                    class="w-full sm:w-auto"
                    :disabled="form.processing"
                >
                    <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                    <LogIn v-else class="size-4" />
                    {{ trans('auth.login') }}
                </Button>
            </div>
        </form>
    </AuthenticationCard>
</template>
