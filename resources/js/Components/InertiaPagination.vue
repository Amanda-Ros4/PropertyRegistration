<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/Components/ui/pagination';

const props = defineProps({
    paginator: { type: Object, required: true },
    routeName: { type: String, required: true },
    routeParams: { type: Object, default: () => ({}) },
    query: { type: Object, default: () => ({}) },
});

const page = computed({
    get: () => props.paginator.current_page,
    set: (value) => {
        router.get(route(props.routeName, props.routeParams), {
            ...props.query,
            page: value,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    },
});
</script>

<template>
    <Pagination
        v-model:page="page"
        :total="paginator.total"
        :items-per-page="paginator.per_page"
        :sibling-count="1"
        show-edges
        class="mx-0 w-auto justify-end"
    >
        <PaginationContent v-slot="{ items }">
            <PaginationPrevious />
            <template v-for="(item, index) in items" :key="index">
                <PaginationItem
                    v-if="item.type === 'page'"
                    :value="item.value"
                    :is-active="item.value === page"
                >
                    {{ item.value }}
                </PaginationItem>
                <PaginationEllipsis v-else :index="index" />
            </template>
            <PaginationNext />
        </PaginationContent>
    </Pagination>
</template>
