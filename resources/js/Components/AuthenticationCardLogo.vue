<script setup>
import { Link } from '@inertiajs/vue3';
import AppBrandMark from '@/Components/AppBrandMark.vue';
</script>

<template>
    <Link :href="'/'">
        <AppBrandMark class="size-16" />
    </Link>
</template>
