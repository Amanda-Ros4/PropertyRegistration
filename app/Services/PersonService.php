<?php

namespace App\Services;

use App\Models\Person;
use App\Models\User;
use App\Support\Digits;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PersonService
{
    public function listForUser(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Person::query()
            ->visibleTo($user);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $digits = preg_replace('/[^0-9]/', '', $search);

            $query->where(function ($q) use ($search, $digits) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('birth_date', 'LIKE', "%{$search}%");

                  if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }

                if ($digits !== '') {
                    $q->orWhere('cpf', 'LIKE', "%{$digits}%")
                      ->orWhere('phone', 'LIKE', "%{$digits}%");
                }
            });
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'LIKE', "%{$filters['name']}%");
        }

        if (!empty($filters['cpf'])) {
            $query->where('cpf', 'LIKE', "%{$filters['cpf']}%");
        }

        if (!empty($filters['birth_date'])) {
            $query->where('birth_date', $filters['birth_date']);
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        return $query->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }
    
    public function allForUser(User $user): Collection
    {
        return Person::query()
            ->visibleTo($user)
            ->orderBy('name')
            ->get(['id', 'name', 'cpf']);
    }

    public function create(User $user, array $data): Person
    {
        return DB::transaction(function () use ($user, $data) {
            $cpf = Digits::only($data['cpf']);
            $userId = $user->id;

            // Check for existing active record (validation should catch this, but enforce here)
            $active = Person::forUser($userId)->where('cpf', $cpf)->first();
            if ($active) {
                throw ValidationException::withMessages([
                    'cpf' => [__('validation.cpf_taken')],
                ]);
            }

            // Check for soft-deleted record: restore and update instead of creating
            $trashed = Person::onlyTrashed()
                ->where('user_id', $userId)
                ->where('cpf', $cpf)
                ->first();

            if ($trashed) {
                $trashed->restore();

                return $this->update($trashed, $data);
            }

            $person = Person::create([
                'user_id' => $userId,
                'name' => $data['name'],
                'birth_date' => $data['birth_date'],
                'cpf' => $cpf,
                'gender' => $data['gender'],
                'phone' => Digits::onlyOrNull($data['phone'] ?? null),
                'email' => $this->normalizeEmail($data['email'] ?? null),
            ]);

            return $person;
        });
    }

    public function update(Person $person, array $data): Person
    {
        return DB::transaction(function () use ($person, $data) {
            $person->update([
                'name' => $data['name'],
                'birth_date' => $data['birth_date'],
                'gender' => $data['gender'],
                'phone' => Digits::onlyOrNull($data['phone'] ?? null),
                'email' => $this->normalizeEmail($data['email'] ?? null),
            ]);

            return $person->fresh();
        });
    }

    public function delete(Person $person): void
    {
        if ($person->properties()->exists()) {
            throw ValidationException::withMessages([
                'person' => [__('people.cannot_delete_with_properties')],
            ]);
        }

        $person->delete();
    }

    private function normalizeEmail(mixed $email): ?string
    {
        $value = trim((string) ($email ?? ''));

        return $value === '' ? null : mb_strtolower($value);
    }
}
