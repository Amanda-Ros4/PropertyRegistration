<?php

namespace App\Policies;

use App\Enums\UserProfile;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function view(User $user, User $model): bool
    {
        return $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function update(User $user, User $model): bool
    {
        if (! $user->canManageUsers()) {
            return false;
        }

        if ($user->isTiAdmin()) {
            return true;
        }

        return $user->isSystemAdmin() && $model->isAttendant();
    }

    public function delete(User $user, User $model): bool
    {
        return false;
    }

    public function assignProfile(User $user, UserProfile $profile): bool
    {
        return $user->canAssignProfile($profile);
    }

    public function viewAudit(User $user): bool
    {
        return $user->canViewAudit();
    }
}
