<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $totalPeople = Person::visibleTo($request->user())->count();
        $totalProperties = Property::visibleTo($request->user())->count();

        $recentPeople = Person::visibleTo($request->user())
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'name', 'cpf', 'gender', 'created_at']);

        $recentProperties = Property::visibleTo($request->user())
            ->with('person:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'person_id', 'street', 'number', 'neighborhood', 'created_at']);

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalPeople' => $totalPeople,
                'totalProperties' => $totalProperties,
            ],
            'recentPeople' => $recentPeople,
            'recentProperties' => $recentProperties,
        ]);
    }
}
