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
        $userId = $request->user()->id;

        $totalPeople = Person::forUser($userId)->count();
        $totalProperties = Property::forUser($userId)->count();

        $recentPeople = Person::forUser($userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'name', 'cpf', 'gender', 'created_at']);

        $recentProperties = Property::forUser($userId)
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
