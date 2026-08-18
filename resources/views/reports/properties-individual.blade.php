<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ __('properties.reports.individual_title', ['id' => $property->id]) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 18px 0 8px; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        .meta { color: #555; margin-bottom: 14px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid th, .grid td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; vertical-align: top; }
        .grid th { width: 32%; background: #f3f4f6; }
        table.list { width: 100%; border-collapse: collapse; }
        table.list th, table.list td { border: 1px solid #ccc; padding: 5px 6px; }
        table.list th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; }
        .num { text-align: right; font-family: DejaVu Sans Mono, monospace; }
    </style>
</head>
<body>
    <h1>{{ __('properties.reports.individual_title', ['id' => $property->id]) }}</h1>
    <p class="meta">{{ __('properties.reports.generated_at') }}: {{ $generatedAt->format('d/m/Y H:i') }}</p>

    <table class="grid">
        <tr>
            <th>{{ __('properties.fields.municipal_registration') }}</th>
            <td>#{{ $property->id }}</td>
        </tr>
        <tr>
            <th>{{ __('properties.fields.owner') }}</th>
            <td>{{ $property->person?->name ?? '—' }}</td>
        </tr>
        <tr>
            <th>{{ __('properties.fields.type') }}</th>
            <td>{{ __('properties.types.'.$property->type->value) }}</td>
        </tr>
        <tr>
            <th>{{ __('properties.fields.status') }}</th>
            <td>{{ __('properties.statuses.'.$property->status->value) }}</td>
        </tr>
        <tr>
            <th>{{ __('properties.fields.land_area') }}</th>
            <td>{{ number_format((float) $property->land_area, 2, ',', '.') }} m²</td>
        </tr>
        <tr>
            <th>{{ __('properties.fields.building_area') }}</th>
            <td>{{ number_format((float) $property->building_area, 2, ',', '.') }} m²</td>
        </tr>
        <tr>
            <th>{{ __('properties.fields.cep') }}</th>
            <td>{{ $property->cep ?: '—' }}</td>
        </tr>
        <tr>
            <th>{{ __('properties.fields.street') }}</th>
            <td>{{ $property->street }}</td>
        </tr>
        <tr>
            <th>{{ __('properties.fields.number') }}</th>
            <td>{{ $property->number }}</td>
        </tr>
        <tr>
            <th>{{ __('properties.fields.neighborhood') }}</th>
            <td>{{ $property->neighborhood }}</td>
        </tr>
        <tr>
            <th>{{ __('properties.fields.complement') }}</th>
            <td>{{ $property->complement ?: '—' }}</td>
        </tr>
    </table>

    <h2>{{ __('properties.endorsements.title') }}</h2>
    <table class="list">
        <thead>
            <tr>
                <th>{{ __('properties.endorsements.fields.date') }}</th>
                <th>{{ __('properties.endorsements.fields.event') }}</th>
                <th>{{ __('properties.endorsements.fields.measure') }}</th>
                <th>{{ __('properties.endorsements.fields.description') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($property->endorsements as $endorsement)
                <tr>
                    <td>{{ optional($endorsement->occurred_on)->format('d/m/Y') }}</td>
                    <td>{{ __($endorsement->event->labelKey()) }}</td>
                    <td class="num">
                        {{ $endorsement->measure === null ? '—' : number_format((float) $endorsement->measure, 2, ',', '.').' m²' }}
                    </td>
                    <td>{{ $endorsement->description }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">{{ __('properties.endorsements.empty') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
