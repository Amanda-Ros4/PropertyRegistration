/**
 * Consulta endereço pelo CEP (8 dígitos) na API pública ViaCEP.
 * @param {string} digits8 — apenas números, tamanho 8
 * @returns {Promise<{ ok: true, data: object } | { ok: false, reason: 'invalid' | 'not_found' | 'network' }>}
 */
export async function fetchAddressByCep(digits8) {
    const d = String(digits8 ?? '').replace(/\D/g, '');
    if (d.length !== 8) {
        return { ok: false, reason: 'invalid' };
    }

    try {
        const res = await fetch(`https://viacep.com.br/ws/${d}/json/`);
        if (!res.ok) {
            return { ok: false, reason: 'network' };
        }
        const data = await res.json();
        if (data.erro) {
            return { ok: false, reason: 'not_found' };
        }
        return { ok: true, data };
    } catch {
        return { ok: false, reason: 'network' };
    }
}
