<script setup>
import { computed, ref, watch, nextTick } from 'vue';
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

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const form = useForm({
    name: '',
    cpf: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

watch(() => form.name, (newValue) => {
    if (!newValue) return;
    const clean = newValue.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
    if (newValue !== clean) {
        nextTick(() => {
            form.name = clean;
        });
    }
});

watch(() => form.cpf, (newValue) => {
    if (!newValue) return;

    let clean = String(newValue).replace(/\D/g, '').substring(0, 11);
    let formatted = clean;

    if (clean.length > 9) {
        formatted = clean.replace(/^(\d{3})(\d{3})(\d{3})(\d{1,2}).*/, '$1.$2.$3-$4');
    } else if (clean.length > 6) {
        formatted = clean.replace(/^(\d{3})(\d{3})(\d{1,3}).*/, '$1.$2.$3');
    } else if (clean.length > 3) {
        formatted = clean.replace(/^(\d{3})(\d{1,3}).*/, '$1.$2');
    }

    if (newValue !== formatted) {
        nextTick(() => {
            form.cpf = formatted;
        });
    }
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
                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required autofocus
                    autocomplete="name" maxlength="100" />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="cpf" :value="trans('people.fields.cpf')" />
                <TextInput id="cpf" v-model="form.cpf" type="text" inputmode="numeric" class="mt-1 block w-full"
                    required autocomplete="off" maxlength="14" :placeholder="trans('people.placeholders.cpf')" />
                <InputError class="mt-2" :message="form.errors.cpf" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" :value="trans('auth.email')" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required
                    autocomplete="username" maxlength="100" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" :value="trans('auth.password')" />
                <div class="relative mt-1">
                    <TextInput id="password" v-model="form.password" maxlength="60"
                        :type="showPassword ? 'text' : 'password'" class="block w-full pr-10" required
                        autocomplete="new-password" />
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68">
                            </path>
                            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                            <line x1="2" x2="22" y1="2" y2="22"></line>
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>

                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    <p>{{ trans('validation.password_rules.length') }}</p>
                    <ul class="list-disc list-inside mt-1">
                        <li>{{ trans('validation.password_rules.uppercase') }}</li>
                        <li>{{ trans('validation.password_rules.lowercase') }}</li>
                        <li>{{ trans('validation.password_rules.symbol') }}</li>
                    </ul>
                </div>

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" :value="trans('auth.password_confirmation')" />
                <div class="relative mt-1">
                    <TextInput id="password_confirmation" v-model="form.password_confirmation" maxlength="60"
                        :type="showPasswordConfirmation ? 'text' : 'password'" class="block w-full pr-10" required
                        autocomplete="new-password" />
                    <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg v-if="!showPasswordConfirmation" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68">
                            </path>
                            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                            <line x1="2" x2="22" y1="2" y2="22"></line>
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div v-if="$page.props.jetstream?.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />
                        <div class="ms-2 text-sm text-gray-600 dark:text-gray-100">
                            I agree to the <a target="_blank" :href="route('terms.show')" :class="AUTH_LINK_CLASS">Terms
                                of
                                Service</a> and <a target="_blank" :href="route('policy.show')"
                                :class="AUTH_LINK_CLASS">Privacy
                                Policy</a>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="mt-6">
                <PrimaryButton type="submit" class="w-full" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
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