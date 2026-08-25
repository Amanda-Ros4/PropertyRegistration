<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AuthTextLink from '@/Components/AuthTextLink.vue';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { AUTH_INTRO_CLASS, AUTH_STATUS_SUCCESS_CLASS } from '@/lib/auth-ui';

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head :title="trans('auth.verify_email.title')" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4" :class="AUTH_INTRO_CLASS">
            {{ trans('auth.verify_email.intro') }}
        </div>

        <div v-if="verificationLinkSent" class="mb-4" :class="AUTH_STATUS_SUCCESS_CLASS">
            {{ trans('auth.verify_email.link_sent') }}
        </div>

        <form @submit.prevent="submit">
            <div class="mt-6">
                <PrimaryButton
                    type="submit"
                    class="w-full"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ trans('auth.verify_email.resend') }}
                </PrimaryButton>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 sm:justify-start">
                <AuthTextLink :href="route('profile.show')">
                    {{ trans('common.profile') }}
                </AuthTextLink>
                <AuthTextLink :href="route('logout')" method="post" as="button">
                    {{ trans('auth.logout') }}
                </AuthTextLink>
            </div>
        </form>
    </AuthenticationCard>
</template>
