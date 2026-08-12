<?php

namespace App\Services;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PropertyService
{
    public function __construct(
        private readonly PropertyDocumentService $documentService,
    ) {}

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
            $property = Property::create([
                'user_id' => $user->id,
                'person_id' => $data['person_id'],
                'type' => $data['type'],
                'land_area' => $this->landAreaForType($data),
                'building_area' => $this->buildingAreaForType($data),
                'cep' => $data['cep'] ?? null,
                'street' => $data['street'],
                'number' => $data['number'],
                'neighborhood' => $data['neighborhood'],
                'complement' => $data['complement'] ?? null,
                'status' => PropertyStatus::Active,
            ]);

            $this->documentService->storeMany($property, $this->uploadedDocuments($data));

            return $property;
        });
    }

    public function update(Property $property, array $data): Property
    {
        return DB::transaction(function () use ($property, $data) {
            $property->update([
                'person_id' => $data['person_id'],
                'type' => $data['type'],
                'land_area' => $this->landAreaForType($data),
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
        $this->documentService->deleteAllForProperty($property);
        $property->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, UploadedFile>
     */
    private function uploadedDocuments(array $data): array
    {
        $documents = $data['documents'] ?? [];

        if (! is_array($documents)) {
            return [];
        }

        return array_values(array_filter(
            $documents,
            fn (mixed $file): bool => $file instanceof UploadedFile,
        ));
    }

    private function typeValue(array $data): ?string
    {
        $type = $data['type'] ?? null;

        if ($type instanceof PropertyType) {
            return $type->value;
        }

        return is_string($type) ? $type : null;
    }

    private function landAreaForType(array $data): mixed
    {
        return $this->typeValue($data) === PropertyType::Apartment->value
            ? 0
            : ($data['land_area'] ?? null);
    }

    private function buildingAreaForType(array $data): mixed
    {
        return $this->typeValue($data) === PropertyType::Land->value
            ? 0
            : ($data['building_area'] ?? null);
    }
}
