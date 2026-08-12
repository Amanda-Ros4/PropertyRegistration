export const PROPERTY_DOCUMENT_MAX_FILES = 5;
export const PROPERTY_DOCUMENT_MAX_BYTES = 3 * 1024 * 1024;
export const PROPERTY_DOCUMENT_ACCEPT = '.jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf';

const ALLOWED_EXTENSIONS = new Set(['jpg', 'jpeg', 'png', 'pdf']);
const ALLOWED_MIMES = new Set(['image/jpeg', 'image/png', 'application/pdf']);

export function fileExtension(file) {
    const name = String(file?.name ?? '');
    const dot = name.lastIndexOf('.');
    return dot >= 0 ? name.slice(dot + 1).toLowerCase() : '';
}

export function isAllowedPropertyDocument(file) {
    const extension = fileExtension(file);
    const mime = String(file?.type ?? '').toLowerCase();

    if (!ALLOWED_EXTENSIONS.has(extension)) {
        return false;
    }

    if (mime && !ALLOWED_MIMES.has(mime)) {
        return false;
    }

    return true;
}

export function formatDocumentSize(bytes) {
    const size = Number(bytes) || 0;
    if (size < 1024) {
        return `${size} B`;
    }
    if (size < 1024 * 1024) {
        return `${(size / 1024).toFixed(1)} KB`;
    }
    return `${(size / (1024 * 1024)).toFixed(1)} MB`;
}

export function documentIconClass(fileOrDoc) {
    const mime = String(fileOrDoc?.mime_type || fileOrDoc?.type || '').toLowerCase();
    const name = String(fileOrDoc?.original_name || fileOrDoc?.name || '').toLowerCase();

    if (mime === 'application/pdf' || name.endsWith('.pdf')) {
        return 'pi pi-file-pdf text-red-500';
    }
    if (mime.startsWith('image/') || /\.(jpe?g|png)$/.test(name)) {
        return 'pi pi-image text-sky-500';
    }
    return 'pi pi-file text-slate-400';
}
