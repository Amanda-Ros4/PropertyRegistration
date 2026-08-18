<?php

namespace App\Services;

use App\Models\Person;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\Digits;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PersonService
{
    public function listForUser(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Person::query()
            ->visibleTo($user)
            ->filter($filters)
            ->orderBy('name')
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

            AuditLogger::record('created', $person, __('audit.descriptions.person_created', [
                'name' => $person->name,
            ]));

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

            AuditLogger::record('updated', $person, __('audit.descriptions.person_updated', [
                'name' => $person->name,
            ]));

            return $person->fresh();
        });
    }

    private function normalizeEmail(mixed $email): ?string
    {
        $value = trim((string) ($email ?? ''));

        return $value === '' ? null : mb_strtolower($value);
    }

    /**
     * Soft-deletes a Person. Throws RuntimeException if the person has active properties.
     */
    public function delete(Person $person): void
    {
        if ($person->hasActiveProperties()) {
            throw new RuntimeException(__('people.has_properties'));
        }

        $person->delete();
    }
}
