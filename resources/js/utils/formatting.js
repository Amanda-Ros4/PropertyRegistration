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

const NAVIGATION_KEYS = new Set([
    'Backspace',
    'Delete',
    'Tab',
    'Escape',
    'Enter',
    'ArrowLeft',
    'ArrowRight',
    'ArrowUp',
    'ArrowDown',
    'Home',
    'End',
]);

/**
 * Bloqueia teclas que não sejam dígitos (mantém navegação e atalhos Ctrl/Cmd/Alt).
 */
export function blockNonDigitKey(event) {
    if (event.ctrlKey || event.metaKey || event.altKey) return;
    if (NAVIGATION_KEYS.has(event.key)) return;
    if (event.key.length === 1 && !/\d/.test(event.key)) {
        event.preventDefault();
    }
}

/**
 * beforeinput: impede digitar não-dígitos (colar fica a cargo do formatador).
 */
export function blockNonDigitBeforeInput(event) {
    if (!event.data) return;
    if (event.inputType === 'insertFromPaste' || event.inputType === 'insertFromDrop') return;
    if (/\D/.test(event.data)) {
        event.preventDefault();
    }
}

/**
 * Bloqueia tudo que não for letra (inclui acentos) ou espaço.
 */
export function blockNonLetterNameKey(event) {
    if (event.ctrlKey || event.metaKey || event.altKey) return;
    if (NAVIGATION_KEYS.has(event.key)) return;
    if (event.key.length === 1 && !/^[\p{L} ]$/u.test(event.key)) {
        event.preventDefault();
    }
}

/**
 * beforeinput: impede digitar o que não for letra/acento/espaço (colar é limpo pelo formatador).
 */
export function blockNonLetterNameBeforeInput(event) {
    if (!event.data) return;
    if (event.inputType === 'insertFromPaste' || event.inputType === 'insertFromDrop') return;
    if (/[^\p{L} ]/u.test(event.data)) {
        event.preventDefault();
    }
}

/**
 * Mantém apenas letras (com acentos) e espaços no nome.
 */
export function formatPersonNameInput(value) {
    return String(value ?? '')
        .replace(/[^\p{L} ]+/gu, '')
        .replace(/\s{2,}/g, ' ');
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

// ─── CEP (Brasil) ────────────────────────────────────────────────────────────

export const CEP_MAX_DIGITS = 8;

/** Com máscara 00000-000 */
export const CEP_INPUT_MAX_LENGTH = 9;

/**
 * Máscara durante digitação: até 8 dígitos → 00000-000
 */
export function formatCepInput(value) {
    const d = stripNonDigits(value, CEP_MAX_DIGITS);
    if (d.length <= 5) return d;
    return `${d.slice(0, 5)}-${d.slice(5)}`;
}

/**
 * Exibe CEP formatado quando há 8 dígitos.
 */
export function formatCepDisplay(cep) {
    if (cep == null || cep === '') return '';
    const digits = stripNonDigits(cep);
    if (digits.length === 8) {
        return `${digits.slice(0, 5)}-${digits.slice(5)}`;
    }
    return String(cep);
}

// ─── Data (máscara MM/DD/YYYY → API) ─────────────────────────────────────────

/** 8 dígitos: MMDDYYYY */
export const BIRTH_DATE_MAX_DIGITS = 8;

/** Com máscara MM/DD/YYYY */
export const BIRTH_DATE_INPUT_MAX_LENGTH = 10;

/**
 * Máscara durante digitação: até 8 dígitos → MM/DD/YYYY
 */
export function formatBirthDateInput(value) {
    const d = stripNonDigits(value, BIRTH_DATE_MAX_DIGITS);
    if (d.length <= 2) return d;
    if (d.length <= 4) return `${d.slice(0, 2)}/${d.slice(2)}`;
    return `${d.slice(0, 2)}/${d.slice(2, 4)}/${d.slice(4)}`;
}

/**
 * Converte ISO YYYY-MM-DD (ou Date) para máscara MM/DD/YYYY.
 */
export function toBirthDateInputValue(value) {
    if (!value) return '';
    if (value instanceof Date && !Number.isNaN(value.getTime())) {
        const m = String(value.getMonth() + 1).padStart(2, '0');
        const d = String(value.getDate()).padStart(2, '0');
        const y = String(value.getFullYear());
        return `${m}/${d}/${y}`;
    }
    const str = String(value);
    const iso = str.substring(0, 10);
    const [y, m, d] = iso.split('-');
    if (!y || !m || !d) return formatBirthDateInput(str);
    return `${m}/${d}/${y}`;
}

/**
 * Interpreta máscara MM/DD/YYYY (ou dígitos) como Date local válida, ou null.
 */
export function parseBirthDateInput(value) {
    const digits = stripNonDigits(value, BIRTH_DATE_MAX_DIGITS);
    if (digits.length !== 8) return null;
    const month = Number(digits.slice(0, 2));
    const day = Number(digits.slice(2, 4));
    const year = Number(digits.slice(4, 8));
    const date = new Date(year, month - 1, day);
    if (
        date.getFullYear() !== year
        || date.getMonth() !== month - 1
        || date.getDate() !== day
    ) {
        return null;
    }
    return date;
}

/**
 * Converte valor do PrimeVue DatePicker ou Date para string YYYY-MM-DD.
 */
export function formatDateForSubmit(date) {
    if (!date) return null;
    if (typeof date === 'string') {
        const parsed = parseBirthDateInput(date);
        if (!parsed) {
            // já pode ser YYYY-MM-DD
            return /^\d{4}-\d{2}-\d{2}$/.test(date.substring(0, 10))
                ? date.substring(0, 10)
                : null;
        }
        return formatDateForSubmit(parsed);
    }
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

/**
 * Converte campo mascarado MM/DD/YYYY para YYYY-MM-DD (API).
 */
export function formatBirthDateForSubmit(value) {
    return formatDateForSubmit(parseBirthDateInput(value));
}
