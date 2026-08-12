<?php

namespace App\Services;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
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
            ->filterById(isset($filters['id']) ? (int) $filters['id'] : null)
            ->filterByType($filters['type'] ?? null)
            ->filterByStreet($filters['street'] ?? null)
            ->filterByNumber($filters['number'] ?? null)
            ->filterByNeighborhood($filters['neighborhood'] ?? null)
            ->filterByPerson(isset($filters['person_id']) ? (int) $filters['person_id'] : null)
            ->filterByStatus($filters['status'] ?? null)
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
                'type' => $data['type'],
                'land_area' => $data['land_area'] ?? null,
                'building_area' => $this->buildingAreaForType($data),
                'cep' => $data['cep'] ?? null,
                'street' => $data['street'],
                'number' => $data['number'],
                'neighborhood' => $data['neighborhood'],
                'complement' => $data['complement'] ?? null,
                'status' => PropertyStatus::Active,
            ]);
        });
    }

    public function update(Property $property, array $data): Property
    {
        return DB::transaction(function () use ($property, $data) {
            $property->update([
                'person_id' => $data['person_id'],
                'type' => $data['type'],
                'land_area' => $data['land_area'] ?? null,
                'building_area' => $this->buildingAreaForType($data),
                'cep' => $data['cep'] ?? null,
                'street' => $data['street'],
                'number' => $data['number'],
                'neighborhood' => $data['neighborhood'],
                'complement' => $data['complement'] ?? null,
            ]);

            return $property->fresh();
        });
    }

    public function updateStatus(Property $property, PropertyStatus $status): Property
    {
        return DB::transaction(function () use ($property, $status) {
            $property->update([
                'status' => $status,
            ]);

            return $property->fresh();
        });
    }

    public function delete(Property $property): void
    {
        $property->delete();
    }

    private function buildingAreaForType(array $data): mixed
    {
        $type = $data['type'] ?? null;
        $isLand = $type === PropertyType::Land || $type === PropertyType::Land->value;

        return $isLand ? 0 : ($data['building_area'] ?? null);
    }
}
