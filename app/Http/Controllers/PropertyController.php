<?php

namespace App\Http\Controllers;

use App\Http\Requests\Properties\StorePropertyRequest;
use App\Http\Requests\Properties\UpdatePropertyRequest;
use App\Models\Property;
use App\Services\PersonService;
use App\Services\PropertyService;
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
        $filters = $request->only(['search', 'person_id']);

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
            ->with('flash', [
                'type' => 'success',
                'message' => __('properties.created'),
            ]);
    }

    public function edit(Request $request, Property $property): Response
    {
        $this->authorize('update', $property);

        $people = $this->personService->allForUser($request->user());

        return Inertia::render('Properties/Edit', [
            'property' => $property->load('person:id,name,cpf'),
            'people' => $people,
        ]);
    }

    public function update(UpdatePropertyRequest $request, Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

        $this->propertyService->update($property, $request->validated());

        return redirect()->route('properties.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('properties.updated'),
            ]);
    }

    public function destroy(Request $request, Property $property): RedirectResponse
    {
        $this->authorize('delete', $property);

        $this->propertyService->delete($property);

        return redirect()->route('properties.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('properties.deleted'),
            ]);
    }
}
