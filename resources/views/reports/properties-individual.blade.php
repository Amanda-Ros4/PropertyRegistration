@extends('reports.layout', ['title' => __('properties.reports.individual_title', ['id' => $property->id])])

@section('content')
    <table class="details">
        <tbody>
            <tr>
                <th>{{ __('properties.fields.municipal_registration') }}</th>
                <td>{{ $property->id }}</td>
            </tr>
            <tr>
                <th>{{ __('properties.fields.owner') }}</th>
                <td>{{ $property->person?->name ?? '-' }}</td>
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
                <td>{{ $property->cep ?: '-' }}</td>
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
                <td>{{ $property->complement ?: '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">{{ Str::upper(__('properties.endorsements.title')) }}</div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 15%">{{ __('properties.endorsements.fields.date') }}</th>
                <th style="width: 22%">{{ __('properties.endorsements.fields.event') }}</th>
                <th class="num" style="width: 17%">{{ __('properties.endorsements.fields.measure') }}</th>
                <th style="width: 46%">{{ __('properties.endorsements.fields.description') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($property->endorsements as $endorsement)
                <tr>
                    <td>{{ optional($endorsement->occurred_on)->format('d/m/Y') }}</td>
                    <td>{{ __($endorsement->event->labelKey()) }}</td>
                    <td class="num">
                        {{ $endorsement->measure === null ? '-' : number_format((float) $endorsement->measure, 2, ',', '.') }}
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
@endsection
