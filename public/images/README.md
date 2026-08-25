# Imagens dos relatórios PDF

Coloque aqui os arquivos usados nos relatórios gerados pelo DomPDF.

## Brasão (obrigatório para aparecer no PDF)

| Arquivo | Uso |
|---------|-----|
| `report-logo.png` | Brasão no cabeçalho dos relatórios sintético e individual |

**Recomendações:** PNG com fundo transparente, cerca de 200×200 px.

O layout lê o arquivo em `resources/views/reports/layout.blade.php` via `public_path('images/report-logo.png')`.

## Testar

1. Salve o brasão como `report-logo.png` nesta pasta.
2. Acesse **Imóveis** → **Relatório PDF**.
3. O brasão deve aparecer centralizado acima do texto da prefeitura.
