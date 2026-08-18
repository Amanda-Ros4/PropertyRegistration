@extends('reports.layout', ['title' => __('properties.reports.synthetic_title')])

@section('content')
    <table>
        <thead>
            <tr>
                <th style="width: 9%">{{ __('properties.fields.municipal_registration') }}</th>
                <th style="width: 16%">{{ __('properties.fields.owner') }}</th>
                <th style="width: 8%">{{ __('properties.fields.type') }}</th>
                <th style="width: 16%">{{ __('properties.fields.street') }}</th>
                <th style="width: 5%">{{ __('properties.fields.number') }}</th>
                <th style="width: 12%">{{ __('properties.fields.neighborhood') }}</th>
                <th class="num" style="width: 11%">{{ __('properties.fields.land_area') }}</th>
                <th class="num" style="width: 12%">{{ __('properties.fields.building_area') }}</th>
                <th style="width: 11%">{{ __('properties.fields.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($properties as $property)
                <tr>
                    <td>{{ $property->id }}</td>
                    <td>{{ $property->person?->name ?? '-' }}</td>
                    <td>{{ __('properties.types.'.$property->type->value) }}</td>
                    <td>{{ $property->street }}</td>
                    <td>{{ $property->number }}</td>
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
@endsection
