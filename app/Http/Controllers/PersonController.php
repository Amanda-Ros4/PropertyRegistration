<?php

namespace App\Http\Controllers;

use App\Http\Requests\People\StorePersonRequest;
use App\Http\Requests\People\UpdatePersonRequest;
use App\Models\Person;
use App\Services\PersonService;
use App\Enums\Gender;
use App\Support\BirthDate;
use App\Support\Digits;
use App\Support\Flash;
use App\Support\SearchInput;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PersonController extends Controller
{
    public function __construct(private readonly PersonService $personService) {}

    public function index(Request $request): Response
    {
        $gender = $request->input('gender');
        $cpfDigits = Digits::only($request->input('cpf'));

        $filters = [
            'name' => SearchInput::sanitize($request->input('name')),
            'birth_date' => BirthDate::toIso($request->input('birth_date')),
            'cpf' => $cpfDigits !== '' ? $cpfDigits : null,
            'gender' => is_string($gender) && in_array($gender, Gender::values(), true) ? $gender : null,
            'search' => SearchInput::sanitize($request->input('search')),
        ];

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
            ->with('flash', Flash::success(__('people.created')));
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
            ->with('flash', Flash::success(__('people.updated')));
    }

    public function destroy(Person $person): RedirectResponse
    {
        $this->authorize('delete', $person);

        try {
            $this->personService->delete($person);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first()
                ?? __('people.cannot_delete_with_properties');

            return redirect()->route('people.index')
                ->with('flash', Flash::error($message));
        }

        return redirect()->route('people.index')
            ->with('flash', Flash::success(__('people.deleted')));
    }
}
