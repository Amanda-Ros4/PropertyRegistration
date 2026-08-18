<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    public static function record(string $action, Model $subject, string $description): void
    {
        /** @var User|null $actor */
        $actor = auth()->user();

        AuditLog::query()->create([
            'user_id' => $actor?->id,
            'user_name' => $actor?->name ?? __('audit.system_user'),
            'user_email' => $actor?->email,
            'action' => $action,
            'auditable_type' => $subject::class,
            'auditable_id' => $subject->getKey(),
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
