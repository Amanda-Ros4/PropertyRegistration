<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AutoDismissAlert from '@/Components/AutoDismissAlert.vue';
import AuthTextLink from '@/Components/AuthTextLink.vue';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { AUTH_INTRO_CLASS } from '@/lib/auth-ui';

const props = defineProps({
    status: String,
});

const page = usePage();
const form = useForm({});

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');

const submit = () => {
    form.post(route('verification.send'));
};

function redirectIfVerified() {
    if (page.props.auth?.user?.email_verified_at) {
        router.visit(route('dashboard'));
    }
}

function checkVerificationStatus() {
    router.visit(route('verification.notice'), {
        replace: true,
        preserveScroll: true,
    });
}

let pollIntervalId;

onMounted(() => {
    redirectIfVerified();

    pollIntervalId = window.setInterval(checkVerificationStatus, 4000);
    document.addEventListener('visibilitychange', onVisibilityChange);
});

onUnmounted(() => {
    window.clearInterval(pollIntervalId);
    document.removeEventListener('visibilitychange', onVisibilityChange);
});

function onVisibilityChange() {
    if (document.visibilityState === 'visible') {
        checkVerificationStatus();
    }
}
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

        <AutoDismissAlert
            v-if="verificationLinkSent"
            :message="trans('auth.verify_email.link_sent')"
        />

        <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
            {{ trans('auth.verify_email.auto_redirect') }}
        </p>

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
