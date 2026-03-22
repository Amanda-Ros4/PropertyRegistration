/**
 * Formatação e sanitização reutilizáveis (CPF, telefone BR, datas em formulários).
 * Para exibição em tabelas/listas use *Display; para máscara em inputs use *Input.
 */

/**
 * Remove pontuação e caracteres não numéricos. Opcionalmente limita o tamanho (ex.: 11 para CPF).
 */
export function stripNonDigits(value, maxLength) {
    const digits = String(value ?? '').replace(/\D/g, '');
    return maxLength != null ? digits.slice(0, maxLength) : digits;
}

// ─── CPF ─────────────────────────────────────────────────────────────────────

/**
 * Máscara durante digitação: até 11 dígitos → 000.000.000-00
 */
export function formatCpfInput(value) {
    const d = stripNonDigits(value, 11);
    if (d.length <= 3) return d;
    if (d.length <= 6) return `${d.slice(0, 3)}.${d.slice(3)}`;
    if (d.length <= 9) return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6)}`;
    return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9)}`;
}

export const CPF_INPUT_MAX_LENGTH = 14;

/**
 * Exibe CPF formatado quando há 11 dígitos; caso contrário devolve o valor original (string).
 */
export function formatCpfDisplay(cpf) {
    if (cpf == null || cpf === '') return '';
    const digits = stripNonDigits(cpf);
    if (digits.length === 11) {
        return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6, 9)}-${digits.slice(9)}`;
    }
    return String(cpf);
}

// ─── Telefone (Brasil, celular/fixo até 11 dígitos) ──────────────────────────

export const PHONE_BR_MAX_DIGITS = 11;

/** Com máscara (99) 99999-9999 — comprimento máximo do valor no input */
export const PHONE_BR_INPUT_MAX_LENGTH = 15;

/**
 * Máscara durante digitação: (DD) NNNNN-NNNN ou (DD) NNNN-NNNN
 */
export function formatPhoneInput(value) {
    const d = stripNonDigits(value, PHONE_BR_MAX_DIGITS);
    if (d.length === 0) return '';
    if (d.length <= 2) return `(${d}`;
    if (d.length <= 7) return `(${d.slice(0, 2)}) ${d.slice(2)}`;
    return `(${d.slice(0, 2)}) ${d.slice(2, 7)}-${d.slice(7)}`;
}

/**
 * Normaliza telefone já salvo para exibição com a mesma máscara do input.
 */
export function formatPhoneDisplay(phone) {
    if (phone == null || phone === '') return '';
    return formatPhoneInput(phone);
}

// ─── Data (DatePicker → API) ─────────────────────────────────────────────────

/**
 * Converte valor do PrimeVue DatePicker para string YYYY-MM-DD.
 */
export function formatDateForSubmit(date) {
    if (!date) return null;
    if (typeof date === 'string') return date;
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}
