<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ __('properties.reports.synthetic_title') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .meta { color: #555; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 5px 6px; text-align: left; }
        th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; }
        .num { text-align: right; font-family: DejaVu Sans Mono, monospace; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h1>{{ __('properties.reports.synthetic_title') }}</h1>
    <p class="meta">{{ __('properties.reports.generated_at') }}: {{ $generatedAt->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>{{ __('properties.fields.municipal_registration') }}</th>
                <th>{{ __('properties.fields.owner') }}</th>
                <th>{{ __('properties.fields.type') }}</th>
                <th>{{ __('properties.fields.street') }}</th>
                <th>{{ __('properties.fields.number') }}</th>
                <th>{{ __('properties.fields.neighborhood') }}</th>
                <th>{{ __('properties.fields.land_area') }}</th>
                <th>{{ __('properties.fields.building_area') }}</th>
                <th>{{ __('properties.fields.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($properties as $property)
                <tr>
                    <td class="num">#{{ $property->id }}</td>
                    <td>{{ $property->person?->name ?? '—' }}</td>
                    <td>{{ __('properties.types.'.$property->type->value) }}</td>
                    <td>{{ $property->street }}</td>
                    <td class="center">{{ $property->number }}</td>
                    <td>{{ $property->neighborhood }}</td>
                    <td class="num">{{ number_format((float) $property->land_area, 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $property->building_area, 2, ',', '.') }}</td>
                    <td>{{ __('properties.statuses.'.$property->status->value) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">{{ __('properties.empty') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
