/**
 * Brazilian CPF mask: at most 11 digits → 000.000.000-00 (14 characters).
 * Builds only from digit positions so extra characters cannot appear after the hyphen.
 */
export function formatCpfInput(value) {
    const d = String(value ?? '').replace(/\D/g, '').slice(0, 11);
    if (d.length <= 3) return d;
    if (d.length <= 6) return `${d.slice(0, 3)}.${d.slice(3)}`;
    if (d.length <= 9) return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6)}`;
    return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9)}`;
}

export const CPF_INPUT_MAX_LENGTH = 14;
