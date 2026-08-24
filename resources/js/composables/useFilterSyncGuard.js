import { ref } from 'vue';

export function useFilterSyncGuard() {
    const syncingFromProps = ref(false);

    function runSyncedFromProps(callback) {
        syncingFromProps.value = true;

        try {
            callback();
        } finally {
            queueMicrotask(() => {
                syncingFromProps.value = false;
            });
        }
    }

    function shouldSkipFilterApply() {
        return syncingFromProps.value;
    }

    return {
        runSyncedFromProps,
        shouldSkipFilterApply,
    };
}
