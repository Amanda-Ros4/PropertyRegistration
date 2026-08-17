<?php

namespace App\Http\Controllers;

use App\Http\Requests\Properties\StorePropertyEndorsementRequest;
use App\Models\Property;
use App\Services\PropertyEndorsementService;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;

class PropertyEndorsementController extends Controller
{
    public function __construct(
        private readonly PropertyEndorsementService $endorsementService,
    ) {}

    public function store(StorePropertyEndorsementRequest $request, Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

        $this->endorsementService->create($property, $request->validated());

        return redirect()->back()
            ->with('flash', Flash::success(__('properties.endorsements.created')));
    }
}
