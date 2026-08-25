<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AuthTextLink from '@/Components/AuthTextLink.vue';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { AUTH_INTRO_CLASS, AUTH_STATUS_SUCCESS_CLASS } from '@/lib/auth-ui';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head :title="trans('auth.forgot_password_page_title')" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4" :class="AUTH_INTRO_CLASS">
            {{ trans('auth.forgot_password_intro') }}
        </div>

        <div v-if="status" class="mb-4" :class="AUTH_STATUS_SUCCESS_CLASS">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" :value="trans('auth.email')" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-6">
                <PrimaryButton
                    type="submit"
                    class="w-full"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ trans('auth.send_reset_link') }}
                </PrimaryButton>
            </div>

            <div class="mt-4 text-center sm:text-left">
                <AuthTextLink :href="route('login')">
                    {{ trans('auth.already_registered') }} {{ trans('auth.login') }}
                </AuthTextLink>
            </div>
        </form>
    </AuthenticationCard>
</template>
