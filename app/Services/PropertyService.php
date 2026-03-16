<?php

namespace App\Services;

use App\Models\Property;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PropertyService
{
    public function listForUser(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Property::query()
            ->with('person:id,name,cpf')
            ->forUser($user->id)
            ->search($filters['search'] ?? null)
            ->filterByPerson(isset($filters['person_id']) ? (int) $filters['person_id'] : null)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(User $user, array $data): Property
    {
        return DB::transaction(function () use ($user, $data) {
            return Property::create([
                'user_id' => $user->id,
                'person_id' => $data['person_id'],
                'street' => $data['street'],
                'number' => $data['number'],
                'neighborhood' => $data['neighborhood'],
                'complement' => $data['complement'] ?? null,
            ]);
        });
    }

    public function update(Property $property, array $data): Property
    {
        return DB::transaction(function () use ($property, $data) {
            $property->update([
                'person_id' => $data['person_id'],
                'street' => $data['street'],
                'number' => $data['number'],
                'neighborhood' => $data['neighborhood'],
                'complement' => $data['complement'] ?? null,
            ]);

            return $property->fresh();
        });
    }

    public function delete(Property $property): void
    {
        $property->delete();
    }
}
