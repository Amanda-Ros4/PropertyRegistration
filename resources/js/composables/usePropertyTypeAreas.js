import { computed, watch } from 'vue';

const ZERO_AREA = '0.00';

/**
 * Regras de área conforme o tipo do imóvel:
 * - Terreno: terreno > 0, edificação = 0 (travada)
 * - Casa: terreno > 0 e edificação > 0
 * - Apartamento: edificação > 0, terreno = 0 (travado)
 */
export function usePropertyTypeAreas(form) {
    const isLand = computed(() => form.type === 'land');
    const isHouse = computed(() => form.type === 'house');
    const isApartment = computed(() => form.type === 'apartment');
    const landAreaRequired = computed(() => isLand.value || isHouse.value);
    const buildingAreaRequired = computed(() => isHouse.value || isApartment.value);
    const landAreaLocked = computed(() => isApartment.value);
    const buildingAreaLocked = computed(() => isLand.value);

    watch(() => form.type, (type, previous) => {
        if (type === 'land') {
            form.building_area = ZERO_AREA;
            form.clearErrors('building_area');
        } else if (previous === 'land') {
            form.building_area = '';
            form.clearErrors('building_area');
        }

        if (type === 'apartment') {
            form.land_area = ZERO_AREA;
            form.clearErrors('land_area');
        } else if (previous === 'apartment') {
            form.land_area = '';
            form.clearErrors('land_area');
        }
    }, { immediate: true });

    function onTypeChange() {
        form.clearErrors('type');
        form.clearErrors('land_area');
        form.clearErrors('building_area');
    }

    function onLandAreaInput(syncMasked, formatAreaInput, value) {
        if (landAreaLocked.value) {
            form.land_area = ZERO_AREA;
            return;
        }
        syncMasked('land_area', formatAreaInput, value);
    }

    function onBuildingAreaInput(syncMasked, formatAreaInput, value) {
        if (buildingAreaLocked.value) {
            form.building_area = ZERO_AREA;
            return;
        }
        syncMasked('building_area', formatAreaInput, value);
    }

    function areasForSubmit(data) {
        if (data.type === 'land') {
            return {
                land_area: data.land_area === '' ? null : data.land_area,
                building_area: 0,
            };
        }

        if (data.type === 'apartment') {
            return {
                land_area: 0,
                building_area: data.building_area === '' ? null : data.building_area,
            };
        }

        return {
            land_area: data.land_area === '' ? null : data.land_area,
            building_area: data.building_area === '' ? null : data.building_area,
        };
    }

    return {
        isLand,
        isHouse,
        isApartment,
        landAreaRequired,
        buildingAreaRequired,
        landAreaLocked,
        buildingAreaLocked,
        onTypeChange,
        onLandAreaInput,
        onBuildingAreaInput,
        areasForSubmit,
        ZERO_AREA,
    };
}
