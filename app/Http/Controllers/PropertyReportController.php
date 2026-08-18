<?php

namespace App\Http\Controllers;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Services\PropertyService;
use App\Support\AddressInput;
use App\Support\Digits;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PropertyReportController extends Controller
{
    public function __construct(private readonly PropertyService $propertyService) {}

    public function synthetic(Request $request): Response
    {
        $this->authorize('viewAny', Property::class);

        $idDigits = Digits::only($request->input('id'));
        $numberDigits = Digits::only($request->input('number'));
        $type = $request->input('type');
        $status = $request->input('status');

        $filters = [
            'id' => $idDigits !== '' ? $idDigits : null,
            'type' => is_string($type) && in_array($type, PropertyType::values(), true) ? $type : null,
            'street' => AddressInput::sanitize($request->input('street')),
            'number' => $numberDigits !== '' ? $numberDigits : null,
            'neighborhood' => AddressInput::sanitize($request->input('neighborhood')),
            'person_id' => $request->filled('person_id') ? (int) $request->input('person_id') : null,
            'status' => is_string($status) && in_array($status, PropertyStatus::values(), true) ? $status : null,
        ];

        $properties = $this->propertyService->allForReport($request->user(), $filters);

        $pdf = Pdf::loadView('reports.properties-synthetic', [
            'properties' => $properties,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('relatorio-sintetico-imoveis.pdf');
    }

    public function individual(Property $property): Response
    {
        $this->authorize('view', $property);

        $property->load([
            'person',
            'endorsements' => fn ($query) => $query->reorder()->orderBy('occurred_on')->orderBy('id'),
        ]);

        $pdf = Pdf::loadView('reports.properties-individual', [
            'property' => $property,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('relatorio-imovel-'.$property->id.'.pdf');
    }
}
