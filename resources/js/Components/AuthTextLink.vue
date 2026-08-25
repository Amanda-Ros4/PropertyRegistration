<script setup>
import { Link } from '@inertiajs/vue3';
import { AUTH_LINK_CLASS } from '@/lib/auth-ui';

defineProps({
    href: { type: String, required: true },
    method: { type: String, default: 'get' },
    as: { type: String, default: 'a' },
});
</script>

<template>
    <Link :href="href" :method="method" :as="as" :class="AUTH_LINK_CLASS">
        <slot />
    </Link>
</template>
