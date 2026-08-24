<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { FilterX, Languages, Moon, Sun } from '@lucide/vue';
import { Button } from '@/Components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { useTheme } from '@/composables/useTheme';
import { useLocale, SUPPORTED_LOCALES } from '@/composables/useLocale';

defineProps({
    showLocaleLabel: { type: Boolean, default: true },
});

const { isDark, toggleTheme } = useTheme();
const { currentLocale, setLocale } = useLocale();

const currentLocaleLabel = computed(
    () => SUPPORTED_LOCALES.find((locale) => locale.code === currentLocale.value)?.label ?? currentLocale.value,
);

async function changeLocale(locale) {
    await setLocale(locale);
    router.reload({
        preserveState: true,
        preserveScroll: true,
        headers: {
            'X-Locale': locale,
        },
    });
}
</script>

<template>
    <div class="flex items-center gap-2">
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="sm" :aria-label="trans('language.label')">
                    <Languages class="size-4" />
                    <span v-if="showLocaleLabel">{{ currentLocaleLabel }}</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuItem v-for="locale in SUPPORTED_LOCALES" :key="locale.code"
                    @click="changeLocale(locale.code)">
                    {{ locale.label }}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>

        <Button variant="ghost" size="icon" :aria-label="isDark ? trans('theme.light') : trans('theme.dark')"
            @click="toggleTheme">
            <Sun v-if="isDark" class="size-4" />
            <Moon v-else class="size-4" />
        </Button>
    </div>
</template>
