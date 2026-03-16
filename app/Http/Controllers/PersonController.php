<?php

namespace App\Http\Controllers;

use App\Http\Requests\People\StorePersonRequest;
use App\Http\Requests\People\UpdatePersonRequest;
use App\Models\Person;
use App\Services\PersonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class PersonController extends Controller
{
    public function __construct(private readonly PersonService $personService) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search']);

        $people = $this->personService->listForUser(
            $request->user(),
            $filters,
            perPage: 15
        );

        return Inertia::render('People/Index', [
            'people' => $people,
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('People/Create');
    }

    public function store(StorePersonRequest $request): RedirectResponse
    {
        $this->personService->create($request->user(), $request->validated());

        return redirect()->route('people.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('people.created'),
            ]);
    }

    public function edit(Person $person): Response
    {
        $this->authorize('update', $person);

        return Inertia::render('People/Edit', [
            'person' => $person,
        ]);
    }

    public function update(UpdatePersonRequest $request, Person $person): RedirectResponse
    {
        $this->authorize('update', $person);

        $this->personService->update($person, $request->validated());

        return redirect()->route('people.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('people.updated'),
            ]);
    }

    public function destroy(Request $request, Person $person): RedirectResponse
    {
        $this->authorize('delete', $person);

        try {
            $this->personService->delete($person);
        } catch (RuntimeException $e) {
            return redirect()->route('people.index')
                ->with('flash', [
                    'type' => 'error',
                    'message' => $e->getMessage(),
                ]);
        }

        return redirect()->route('people.index')
            ->with('flash', [
                'type' => 'success',
                'message' => __('people.deleted'),
            ]);
    }
}
