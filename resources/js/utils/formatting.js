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

export const AUTHENTICATOR_CODE_LENGTH = 6;

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
 * Área em m²: apenas números com até 2 casas decimais (aceita vírgula ou ponto).
 */
export function formatAreaInput(value) {
    let raw = String(value ?? '').replace(/[^\d.,]/g, '');
    raw = raw.replace(',', '.');
    const firstDot = raw.indexOf('.');
    if (firstDot === -1) {
        return raw;
    }
    const intPart = raw.slice(0, firstDot).replace(/\./g, '');
    const decPart = raw.slice(firstDot + 1).replace(/\./g, '').slice(0, 2);
    return decPart.length > 0 || raw.endsWith('.')
        ? `${intPart}.${decPart}`
        : intPart;
}

export function blockNonAreaKey(event) {
    if (event.ctrlKey || event.metaKey || event.altKey) return;
    if (NAVIGATION_KEYS.has(event.key)) return;
    if (event.key.length === 1 && !/[\d.,]/.test(event.key)) {
        event.preventDefault();
    }
}

export function blockNonAreaBeforeInput(event) {
    if (!event.data) return;
    if (event.inputType === 'insertFromPaste' || event.inputType === 'insertFromDrop') return;
    if (/[^\d.,]/.test(event.data)) {
        event.preventDefault();
    }
}

/**
 * Caracteres permitidos em campos de pesquisa:
 * letras, números, espaços e símbolos de sistemas métrico/imperial
 * e de topografia/geometria.
 */
const SEARCH_ALLOWED_CHAR_PATTERN = /[\p{L}\p{N}\s°′″'"/\\\-.,:()·×÷±²³µ%‰∠△▲▼◆○●≈≠≤≥]/u;

export function isAllowedSearchChar(char) {
    return SEARCH_ALLOWED_CHAR_PATTERN.test(char);
}

/**
 * Remove caracteres especiais não permitidos da pesquisa.
 */
export function formatSearchInput(value) {
    return String(value ?? '')
        .split('')
        .filter((char) => isAllowedSearchChar(char))
        .join('')
        .replace(/\s{2,}/g, ' ');
}

export function blockDisallowedSearchKey(event) {
    if (event.ctrlKey || event.metaKey || event.altKey) return;
    if (NAVIGATION_KEYS.has(event.key)) return;
    if (event.key.length === 1 && !isAllowedSearchChar(event.key)) {
        event.preventDefault();
    }
}

export function blockDisallowedSearchBeforeInput(event) {
    if (!event.data) return;
    if (event.inputType === 'insertFromPaste' || event.inputType === 'insertFromDrop') return;
    if (![...event.data].every(isAllowedSearchChar)) {
        event.preventDefault();
    }
}

/**
 * Logradouro/bairro: letras, números, espaços e símbolos métricos/imperiais/topográficos.
 * Bloqueia especiais como @ # $ %.
 */
const ADDRESS_ALLOWED_CHAR_PATTERN = /[\p{L}\p{N}\s°ºª′″'"/\\\-.,:()·×÷±²³µ∠△▲▼◆○●≈≠≤≥]/u;

export function isAllowedAddressChar(char) {
    return ADDRESS_ALLOWED_CHAR_PATTERN.test(char);
}

export function formatAddressInput(value) {
    return String(value ?? '')
        .split('')
        .filter((char) => isAllowedAddressChar(char))
        .join('')
        .replace(/\s{2,}/g, ' ');
}

export function blockDisallowedAddressKey(event) {
    if (event.ctrlKey || event.metaKey || event.altKey) return;
    if (NAVIGATION_KEYS.has(event.key)) return;
    if (event.key.length === 1 && !isAllowedAddressChar(event.key)) {
        event.preventDefault();
    }
}

export function blockDisallowedAddressBeforeInput(event) {
    if (!event.data) return;
    if (event.inputType === 'insertFromPaste' || event.inputType === 'insertFromDrop') return;
    if (![...event.data].every(isAllowedAddressChar)) {
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

// ─── Data (máscara DD/MM/YYYY → API) ─────────────────────────────────────────

/** 8 dígitos: DDMMYYYY */
export const BIRTH_DATE_MAX_DIGITS = 8;

/** Com máscara DD/MM/YYYY */
export const BIRTH_DATE_INPUT_MAX_LENGTH = 10;

/**
 * Máscara durante digitação: até 8 dígitos → DD/MM/YYYY
 */
export function formatBirthDateInput(value) {
    const d = stripNonDigits(value, BIRTH_DATE_MAX_DIGITS);
    if (d.length <= 2) return d;
    if (d.length <= 4) return `${d.slice(0, 2)}/${d.slice(2)}`;
    return `${d.slice(0, 2)}/${d.slice(2, 4)}/${d.slice(4)}`;
}

/**
 * Converte ISO YYYY-MM-DD (ou Date) para máscara DD/MM/YYYY.
 */
export function toBirthDateInputValue(value) {
    if (!value) return '';
    if (value instanceof Date && !Number.isNaN(value.getTime())) {
        const day = String(value.getDate()).padStart(2, '0');
        const month = String(value.getMonth() + 1).padStart(2, '0');
        const year = String(value.getFullYear());
        return `${day}/${month}/${year}`;
    }
    const str = String(value);
    const iso = str.substring(0, 10);
    const [y, m, d] = iso.split('-');
    if (!y || !m || !d) return formatBirthDateInput(str);
    return `${d}/${m}/${y}`;
}

/**
 * Interpreta máscara DD/MM/YYYY (ou dígitos) como Date local válida, ou null.
 */
export function parseBirthDateInput(value) {
    const digits = stripNonDigits(value, BIRTH_DATE_MAX_DIGITS);
    if (digits.length !== 8) return null;
    const day = Number(digits.slice(0, 2));
    const month = Number(digits.slice(2, 4));
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
 * Converte locale da app (pt_BR) para tag BCP 47 usada por Intl (pt-BR).
 */
export function toIntlLocale(locale) {
    if (!locale) {
        return 'pt-BR';
    }

    if (locale === 'pt_BR') {
        return 'pt-BR';
    }

    return String(locale).includes('_') ? String(locale).replace('_', '-') : locale;
}

/**
 * Exibe data/hora ISO com locale do usuário (listas, detalhes).
 */
export function formatDateTimeDisplay(value, locale = 'pt_BR') {
    if (!value) {
        return '—';
    }

    const date = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(date.getTime())) {
        return String(value);
    }

    return date.toLocaleString(toIntlLocale(locale));
}

/**
 * Exibe data ISO YYYY-MM-DD sem deslocar o fuso (lista/tabelas).
 */
export function formatDateDisplay(value) {
    if (!value) return '—';
    if (value instanceof Date && !Number.isNaN(value.getTime())) {
        return toBirthDateInputValue(value);
    }
    const iso = String(value).substring(0, 10);
    const [y, m, d] = iso.split('-');
    if (!y || !m || !d) return String(value);
    return `${d}/${m}/${y}`;
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
