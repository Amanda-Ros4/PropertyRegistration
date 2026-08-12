<?php

namespace App\Services;

use App\Enums\ActiveStatus;
use App\Enums\UserProfile;
use App\Models\User;
use App\Support\Digits;
use App\Support\SearchInput;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $search = SearchInput::sanitize($filters['search'] ?? null);

        return User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('cpf', 'like', '%'.Digits::only($search).'%');
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array<int, array{value: string, label_key: string}>
     */
    public function profileOptionsFor(User $actor): array
    {
        $profiles = match (true) {
            $actor->isTiAdmin() => UserProfile::cases(),
            $actor->isSystemAdmin() => [UserProfile::Attendant],
            default => [],
        };

        return array_map(
            fn (UserProfile $profile) => [
                'value' => $profile->value,
                'label_key' => $profile->labelKey(),
            ],
            $profiles,
        );
    }

    public function create(User $actor, array $data): User
    {
        $profile = UserProfile::from($data['profile']);

        if (! $actor->can('assignProfile', $profile)) {
            throw ValidationException::withMessages([
                'profile' => [__('validation.user_profile_not_allowed')],
            ]);
        }

        return DB::transaction(function () use ($data, $profile) {
            return User::create([
                'name' => $data['name'],
                'cpf' => Digits::only($data['cpf']),
                'email' => mb_strtolower(trim($data['email'])),
                'password' => Hash::make($data['password']),
                'profile' => $profile,
                'active' => ActiveStatus::from($data['active']),
            ]);
        });
    }

    public function update(User $actor, User $user, array $data): User
    {
        return DB::transaction(function () use ($actor, $user, $data) {
            $profile = UserProfile::from($data['profile']);

            if (! $actor->can('assignProfile', $profile)) {
                throw ValidationException::withMessages([
                    'profile' => [__('validation.user_profile_not_allowed')],
                ]);
            }

            if ((int) $actor->id === (int) $user->id && $profile !== $user->profile) {
                throw ValidationException::withMessages([
                    'profile' => [__('validation.user_profile_self_change')],
                ]);
            }

            $payload = [
                'name' => $data['name'],
                'cpf' => Digits::only($data['cpf']),
                'email' => mb_strtolower(trim($data['email'])),
                'profile' => $profile,
                'active' => ActiveStatus::from($data['active']),
            ];

            if ((int) $actor->id === (int) $user->id && $payload['active'] === ActiveStatus::Inactive) {
                throw ValidationException::withMessages([
                    'active' => [__('validation.user_cannot_deactivate_self')],
                ]);
            }

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);

            return $user->fresh();
        });
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
