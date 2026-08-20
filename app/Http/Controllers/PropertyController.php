<?php

namespace App\Http\Controllers;

use App\Http\Requests\Properties\StorePropertyRequest;
use App\Http\Requests\Properties\UpdatePropertyRequest;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Services\PersonService;
use App\Services\PropertyService;
use App\Support\AddressInput;
use App\Support\Digits;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PropertyController extends Controller
{
    public function __construct(
        private readonly PropertyService $propertyService,
        private readonly PersonService $personService,
    ) {}

    public function index(Request $request): Response
    {
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

        $properties = $this->propertyService->listForUser(
            $request->user(),
            $filters,
            perPage: 15
        );

        $people = $this->personService->allForUser($request->user());

        return Inertia::render('Properties/Index', [
            'properties' => $properties,
            'people' => $people,
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): Response
    {
        $people = $this->personService->allForUser($request->user());

        return Inertia::render('Properties/Create', [
            'people' => $people,
        ]);
    }

    public function store(StorePropertyRequest $request): RedirectResponse
    {
        $this->propertyService->create($request->user(), $request->validated());

        return redirect()->route('properties.index')
            ->with('flash', Flash::success(__('properties.created')));
    }

    public function edit(Request $request, Property $property): Response
    {
        $this->authorize('view', $property);

        $people = $this->personService->allForUser($request->user());

        return Inertia::render('Properties/Edit', [
            'property' => $property->load(['person:id,name,cpf', 'documents', 'endorsements']),
            'people' => $people,
        ]);
    }

    public function update(UpdatePropertyRequest $request, Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

        $this->propertyService->update($property, $request->validated());

        return redirect()->route('properties.index')
            ->with('flash', Flash::success(__('properties.updated')));
    }

    public function destroy(Property $property): RedirectResponse
    {
        $this->authorize('delete', $property);

        $this->propertyService->delete($property);

        return redirect()->route('properties.index')
            ->with('flash', Flash::success(__('properties.deleted')));
    }
}
