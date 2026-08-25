<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { CloudUpload, Download, Loader2, Paperclip, Trash2, X } from '@lucide/vue';
import FormField from '@/Components/FormField.vue';
import DeleteConfirmation from '@/Components/DeleteConfirmation.vue';
import { Button } from '@/Components/ui/button';
import { resolveAppIcon } from '@/lib/app-icons';
import { useAppToast } from '@/composables/useAppToast';
import {
    PROPERTY_DOCUMENT_ACCEPT,
    PROPERTY_DOCUMENT_MAX_BYTES,
    PROPERTY_DOCUMENT_MAX_FILES,
    documentIconType,
    downloadPendingFile,
    fileExtension,
    formatDocumentSize,
    isAllowedPropertyDocument,
} from '@/utils/propertyDocuments';

const props = defineProps({
    documents: { type: Array, default: () => [] },
    propertyId: { type: [Number, String], default: null },
    errors: { type: Object, default: () => ({}) },
});

const pending = defineModel({ type: Array, default: () => [] });

const { addErrorToast, showValidationErrorToast } = useAppToast();
const fileInputRef = ref(null);
const deleteConfirmRef = ref(null);
const documentToDelete = ref(null);
const uploading = ref(false);
const deleting = ref(false);
const dragDepth = ref(0);

const dragActive = computed(() => dragDepth.value > 0);

const existingDocuments = computed(() => props.documents ?? []);
const isEdit = computed(() => props.propertyId != null && props.propertyId !== '');
const totalCount = computed(() => existingDocuments.value.length + pending.value.length);
const remainingSlots = computed(() => Math.max(0, PROPERTY_DOCUMENT_MAX_FILES - totalCount.value));
const atLimit = computed(() => remainingSlots.value === 0);
const dropZoneDisabled = computed(() => atLimit.value || uploading.value);
const countLabel = computed(() =>
    trans('properties.documents.count', {
        count: totalCount.value,
        max: PROPERTY_DOCUMENT_MAX_FILES,
    }),
);

const fieldError = computed(() => {
    const errors = props.errors ?? {};
    if (errors.documents) {
        return Array.isArray(errors.documents) ? errors.documents[0] : errors.documents;
    }
    const key = Object.keys(errors).find((name) => name.startsWith('documents.'));
    if (!key) {
        return null;
    }
    const value = errors[key];
    return Array.isArray(value) ? value[0] : value;
});

function iconFor(fileOrDoc) {
    return resolveAppIcon(documentIconType(fileOrDoc));
}

function iconClassFor(fileOrDoc) {
    const type = documentIconType(fileOrDoc);

    if (type === 'file-pdf') {
        return 'text-red-500';
    }
    if (type === 'image') {
        return 'text-sky-500';
    }
    return 'text-slate-400';
}

function nextPendingId() {
    return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

function previewFor(file) {
    if (file?.type?.startsWith('image/')) {
        return URL.createObjectURL(file);
    }
    return null;
}

function revokePreview(item) {
    if (item?.previewUrl) {
        URL.revokeObjectURL(item.previewUrl);
    }
}

function openFilePicker() {
    if (atLimit.value || uploading.value) {
        return;
    }
    fileInputRef.value?.click();
}

function rejectFile(file, reasonKey) {
    addErrorToast(trans(reasonKey, { name: file.name }));
}

function collectValidFiles(fileList) {
    const selected = Array.from(fileList ?? []);
    const accepted = [];

    for (const file of selected) {
        if (accepted.length >= remainingSlots.value) {
            addErrorToast(trans('properties.documents.max_files', { max: PROPERTY_DOCUMENT_MAX_FILES }));
            break;
        }

        if (!isAllowedPropertyDocument(file) || !fileExtension(file)) {
            rejectFile(file, 'properties.documents.invalid_type');
            continue;
        }

        if (file.size > PROPERTY_DOCUMENT_MAX_BYTES) {
            rejectFile(file, 'properties.documents.too_large');
            continue;
        }

        accepted.push(file);
    }

    return accepted;
}

function processFiles(fileList) {
    const accepted = collectValidFiles(fileList);

    if (!accepted.length) {
        return;
    }

    if (isEdit.value) {
        uploadFiles(accepted);
        return;
    }

    pending.value = [
        ...pending.value,
        ...accepted.map((file) => ({
            id: nextPendingId(),
            file,
            name: file.name,
            size: file.size,
            previewUrl: previewFor(file),
        })),
    ];
}

function onFileChange(event) {
    const input = event.target;
    processFiles(input.files);
    input.value = '';
}

function onDragEnter(event) {
    if (dropZoneDisabled.value || !hasFilesInTransfer(event)) {
        return;
    }
    dragDepth.value += 1;
}

function onDragLeave() {
    dragDepth.value = Math.max(0, dragDepth.value - 1);
}

function onDragOver(event) {
    if (dropZoneDisabled.value || !hasFilesInTransfer(event)) {
        return;
    }
    event.dataTransfer.dropEffect = 'copy';
}

function onDrop(event) {
    dragDepth.value = 0;

    if (dropZoneDisabled.value) {
        return;
    }

    processFiles(event.dataTransfer?.files);
}

function hasFilesInTransfer(event) {
    return Array.from(event.dataTransfer?.types ?? []).includes('Files');
}

function onDropZoneClick() {
    if (dropZoneDisabled.value) {
        return;
    }
    openFilePicker();
}

function onDropZoneKeydown(event) {
    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        onDropZoneClick();
    }
}

function documentPreviewUrl(document) {
    return document.preview_url || document.download_url;
}

function documentDownloadUrl(document) {
    return document.download_url;
}

function downloadSaved(document) {
    window.location.assign(documentDownloadUrl(document));
}

function removePending(item) {
    revokePreview(item);
    pending.value = pending.value.filter((entry) => entry.id !== item.id);
}

function uploadFiles(files) {
    uploading.value = true;
    router.post(route('properties.documents.store', props.propertyId), {
        documents: files,
    }, {
        forceFormData: true,
        preserveScroll: true,
        onError: showValidationErrorToast,
        onFinish: () => {
            uploading.value = false;
        },
    });
}

function confirmDelete(document) {
    documentToDelete.value = document;
    deleteConfirmRef.value?.open();
}

function deleteDocument() {
    if (!documentToDelete.value || deleting.value) {
        return;
    }

    deleting.value = true;
    router.delete(route('properties.documents.destroy', {
        property: props.propertyId,
        document: documentToDelete.value.id,
    }), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            documentToDelete.value = null;
        },
    });
}

onBeforeUnmount(() => {
    pending.value.forEach(revokePreview);
});
</script>

<template>
    <FormField
        class="md:col-span-2"
        :label="trans('properties.fields.documents')"
        :error="fieldError"
        :hint="trans('properties.documents.hint')"
    >
        <input
            ref="fileInputRef"
            type="file"
            class="hidden"
            :accept="PROPERTY_DOCUMENT_ACCEPT"
            multiple
            :disabled="atLimit || uploading"
            @change="onFileChange"
        />

        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                {{ countLabel }}
            </p>
            <Button
                type="button"
                variant="outline"
                size="sm"
                :disabled="atLimit || uploading"
                @click="openFilePicker"
            >
                <Loader2 v-if="uploading" class="size-4 animate-spin" />
                <Paperclip v-else class="size-4" />
                {{ trans('properties.documents.attach') }}
            </Button>
        </div>

        <ul v-if="existingDocuments.length || pending.length" class="space-y-2 mb-3">
            <li
                v-for="document in existingDocuments"
                :key="`saved-${document.id}`"
                class="flex items-center gap-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 px-3 py-2"
            >
                <img
                    v-if="document.is_image"
                    :src="documentPreviewUrl(document)"
                    :alt="document.original_name"
                    class="h-11 w-11 rounded object-cover border border-slate-200 dark:border-slate-700 shrink-0"
                />
                <component
                    v-else
                    :is="iconFor(document)"
                    :class="['size-5 shrink-0', iconClassFor(document)]"
                />
                <div class="min-w-0 flex-1">
                    <a
                        :href="documentDownloadUrl(document)"
                        class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate hover:text-green-700 dark:hover:text-green-400"
                        :title="trans('properties.documents.download')"
                    >
                        {{ document.original_name }}
                    </a>
                    <p class="text-xs text-slate-500">
                        {{ document.human_size || formatDocumentSize(document.size) }}
                    </p>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :aria-label="trans('properties.documents.download')"
                    @click="downloadSaved(document)"
                >
                    <Download class="size-4" />
                    {{ trans('properties.documents.download') }}
                </Button>
                <Button
                    v-if="isEdit"
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    class="text-destructive hover:text-destructive"
                    :disabled="deleting"
                    :aria-label="trans('common.delete')"
                    @click="confirmDelete(document)"
                >
                    <Trash2 class="size-4" />
                </Button>
            </li>

            <li
                v-for="item in pending"
                :key="`pending-${item.id}`"
                class="flex items-center gap-3 rounded-lg border border-dashed border-slate-200 dark:border-slate-700 px-3 py-2"
            >
                <img
                    v-if="item.previewUrl"
                    :src="item.previewUrl"
                    :alt="item.name"
                    class="h-11 w-11 rounded object-cover border border-slate-200 dark:border-slate-700 shrink-0"
                />
                <component
                    v-else
                    :is="iconFor(item.file)"
                    :class="['size-5 shrink-0', iconClassFor(item.file)]"
                />
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate">
                        {{ item.name }}
                    </p>
                    <p class="text-xs text-slate-500">
                        {{ formatDocumentSize(item.size) }}
                        · {{ trans('properties.documents.pending') }}
                    </p>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :aria-label="trans('properties.documents.download')"
                    @click="downloadPendingFile(item)"
                >
                    <Download class="size-4" />
                    {{ trans('properties.documents.download') }}
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    :aria-label="trans('common.delete')"
                    @click="removePending(item)"
                >
                    <X class="size-4" />
                </Button>
            </li>
        </ul>

        <div
            v-if="!atLimit"
            role="button"
            tabindex="0"
            class="rounded-lg border border-dashed px-4 py-6 text-center text-sm transition-colors"
            :class="[
                dragActive
                    ? 'border-green-500 bg-green-50 dark:bg-green-950/30 text-green-700 dark:text-green-300'
                    : 'border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-900/40',
                dropZoneDisabled ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer',
            ]"
            :aria-label="trans('properties.documents.drop_zone_label')"
            @click="onDropZoneClick"
            @keydown="onDropZoneKeydown"
            @dragenter.prevent="onDragEnter"
            @dragleave.prevent="onDragLeave"
            @dragover.prevent="onDragOver"
            @drop.prevent="onDrop"
        >
            <CloudUpload
                class="size-5 mx-auto mb-2"
                :class="dragActive ? 'text-green-600 dark:text-green-400' : 'text-slate-400'"
                aria-hidden="true"
            />
            <p>
                {{
                    existingDocuments.length || pending.length
                        ? trans('properties.documents.drop_zone_add')
                        : trans('properties.documents.drop_zone_empty')
                }}
            </p>
        </div>

        <DeleteConfirmation
            ref="deleteConfirmRef"
            :title="trans('properties.documents.delete_confirm_title')"
            :message="trans('properties.documents.delete_confirm_message')"
            @confirm="deleteDocument"
        />
    </FormField>
</template>
