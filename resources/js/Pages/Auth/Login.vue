<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AuthTextLink from '@/Components/AuthTextLink.vue';
import AutoDismissAlert from '@/Components/AutoDismissAlert.vue';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    canRegister: { type: Boolean, default: true },
    status: String,
});

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head :title="trans('auth.login')" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <AutoDismissAlert :message="status ?? ''" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" :value="trans('auth.email')" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autofocus
                    autocomplete="username" maxlength="100" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" :value="trans('auth.password')" />

                <div class="relative mt-1">
                    <TextInput id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                        class="block w-full pr-10" required autocomplete="current-password" maxlength="100" />

                    <button type="button"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700"
                        @click="showPassword = !showPassword">
                        <!-- Ícone de olho aberto (quando a senha está oculta) -->
                        <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>

                        <!-- Ícone de olho com risco (quando a senha está visível) -->
                        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <InputError class="mt-2" :message="form.errors.password" />
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-100">{{ trans('auth.remember_me') }}</span>
                </label>
                <AuthTextLink v-if="canResetPassword" :href="route('password.request')">
                    {{ trans('auth.forgot_password') }}
                </AuthTextLink>
            </div>

            <div class="mt-6">
                <PrimaryButton type="submit" class="w-full" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    {{ trans('auth.login') }}
                </PrimaryButton>
            </div>

            <div v-if="canRegister" class="mt-4 text-center sm:text-left">
                <AuthTextLink :href="route('register')">
                    {{ trans('auth.no_account') }} {{ trans('auth.register') }}
                </AuthTextLink>
            </div>
        </form>
    </AuthenticationCard>
</template>
