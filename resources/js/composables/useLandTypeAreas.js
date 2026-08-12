import { computed, watch } from 'vue';

const LAND_BUILDING_AREA = '0.00';

/**
 * Quando o tipo é Terreno: área do terreno obrigatória e área da edificação travada em zero.
 */
export function useLandTypeAreas(form) {
    const isLand = computed(() => form.type === 'land');

    watch(isLand, (land) => {
        if (! land) {
            return;
        }

        form.building_area = LAND_BUILDING_AREA;
        form.clearErrors('building_area');
    }, { immediate: true });

    function onTypeChange() {
        form.clearErrors('type');
        form.clearErrors('land_area');
        form.clearErrors('building_area');
    }

    return { isLand, onTypeChange, LAND_BUILDING_AREA };
}
