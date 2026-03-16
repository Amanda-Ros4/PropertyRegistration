import { ref, watchEffect } from 'vue';

const THEME_KEY = 'app_theme';
const DARK = 'dark';
const LIGHT = 'light';

function getInitialTheme() {
    if (typeof window === 'undefined') return LIGHT;
    const stored = localStorage.getItem(THEME_KEY);
    if (stored) return stored;
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? DARK : LIGHT;
}

const theme = ref(typeof window !== 'undefined' ? getInitialTheme() : LIGHT);

if (typeof window !== 'undefined') {
    applyTheme(theme.value);
}

function applyTheme(value) {
    if (value === DARK) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    localStorage.setItem(THEME_KEY, value);
}

export function useTheme() {
    const isDark = ref(theme.value === DARK);

    watchEffect(() => {
        theme.value = isDark.value ? DARK : LIGHT;
        if (typeof window !== 'undefined') {
            applyTheme(theme.value);
        }
    });

    function toggleTheme() {
        isDark.value = !isDark.value;
    }

    return { isDark, toggleTheme };
}
