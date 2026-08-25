@extends('reports.layout', ['title' => __('properties.reports.synthetic_title')])

@section('content')
    <table class="report-table">
        <colgroup>
            <col style="width: 5%">
            <col style="width: 15%">
            <col style="width: 8%">
            <col style="width: 20%">
            <col style="width: 5%">
            <col style="width: 14%">
            <col style="width: 11%">
            <col style="width: 11%">
            <col style="width: 11%">
        </colgroup>
        <thead>
            <tr>
                <th>{{ __('properties.reports.columns.registration') }}</th>
                <th>{{ __('properties.reports.columns.owner') }}</th>
                <th>{{ __('properties.reports.columns.type') }}</th>
                <th>{{ __('properties.reports.columns.street') }}</th>
                <th>{{ __('properties.reports.columns.number') }}</th>
                <th>{{ __('properties.reports.columns.neighborhood') }}</th>
                <th class="num">{{ __('properties.reports.columns.land_area') }}</th>
                <th class="num">{{ __('properties.reports.columns.building_area') }}</th>
                <th>{{ __('properties.reports.columns.status') }}</th>
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
                    <td class="num">{{ \App\Support\ReportFormatting::area($property->land_area) }}</td>
                    <td class="num">{{ \App\Support\ReportFormatting::area($property->building_area) }}</td>
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
