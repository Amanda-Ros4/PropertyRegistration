<?php

namespace App\Actions\Jetstream;

use App\Models\Person;
use App\Models\Property;
use App\Models\User;
use App\Services\PropertyDocumentService;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user and all tenant data (imóveis antes de pessoas por causa do FK person_id).
     */
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $userId = $user->id;
            $documentService = app(PropertyDocumentService::class);

            Property::withTrashed()->where('user_id', $userId)->get()->each(
                fn (Property $property) => $documentService->deleteAllForProperty($property),
            );

            Property::withTrashed()->where('user_id', $userId)->forceDelete();
            Person::withTrashed()->where('user_id', $userId)->forceDelete();

            $user->deleteProfilePhoto();
            $user->tokens->each->delete();
            $user->delete();
        });
    }
}
