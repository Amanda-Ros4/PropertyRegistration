<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AuthTextLink from '@/Components/AuthTextLink.vue';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { AUTH_LINK_CLASS } from '@/lib/auth-ui';
import { CPF_INPUT_MAX_LENGTH, formatCpfInput } from '@/utils/formatting';

const form = useForm({
    name: '',
    cpf: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const cpfModel = computed({
    get() {
        return form.cpf;
    },
    set(value) {
        form.cpf = formatCpfInput(value);
    },
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head :title="trans('auth.register')" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" :value="trans('common.name')" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="cpf" :value="trans('people.fields.cpf')" />
                <TextInput
                    id="cpf"
                    v-model="cpfModel"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    inputmode="numeric"
                    autocomplete="off"
                    :maxlength="CPF_INPUT_MAX_LENGTH"
                    :placeholder="trans('people.placeholders.cpf')"
                />
                <InputError class="mt-2" :message="form.errors.cpf" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" :value="trans('auth.email')" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" :value="trans('auth.password')" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" :value="trans('auth.password_confirmation')" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div v-if="$page.props.jetstream?.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

                        <div class="ms-2 text-sm text-gray-600 dark:text-gray-100">
                            I agree to the <a target="_blank" :href="route('terms.show')" :class="AUTH_LINK_CLASS">Terms of Service</a> and <a target="_blank" :href="route('policy.show')" :class="AUTH_LINK_CLASS">Privacy Policy</a>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="mt-6">
                <PrimaryButton
                    type="submit"
                    class="w-full"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ trans('auth.register') }}
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
