<?php

namespace App\Services;

use App\Models\Audit;
use App\Models\User;
use App\Support\AuditableTypes;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class AuditService
{
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $userId = isset($filters['user_id']) ? (int) $filters['user_id'] : null;
        $event = is_string($filters['event'] ?? null) ? $filters['event'] : null;
        $auditableType = is_string($filters['auditable_type'] ?? null) ? $filters['auditable_type'] : null;
        $date = is_string($filters['date'] ?? null) ? $filters['date'] : null;

        return Audit::query()
            ->with('user:id,name,email')
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->when($event, fn ($query) => $query->where('event', $event))
            ->when($auditableType, fn ($query) => $query->where('auditable_type', $auditableType))
            ->when($date, function ($query) use ($date) {
                $query->whereDate('created_at', Carbon::parse($date)->toDateString());
            })
            ->whereIn('auditable_type', AuditableTypes::classes())
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Audit $audit) => $this->transformForList($audit));
    }

    public function transformForList(Audit $audit): array
    {
        return [
            'id' => $audit->id,
            'event' => $audit->event,
            'created_at' => $audit->created_at?->toIso8601String(),
            'auditable_type' => $audit->auditable_type,
            'table_label_key' => $audit->tableLabelKey(),
            'auditable_id' => $audit->auditable_id,
            'user_name' => $audit->user?->name ?? __('audit.system_user'),
            'user_email' => $audit->user?->email,
        ];
    }

    public function transformForShow(Audit $audit): array
    {
        return [
            'id' => $audit->id,
            'event' => $audit->event,
            'created_at' => $audit->created_at?->toIso8601String(),
            'auditable_type' => $audit->auditable_type,
            'table_label_key' => $audit->tableLabelKey(),
            'auditable_id' => $audit->auditable_id,
            'user_name' => $audit->user?->name ?? __('audit.system_user'),
            'user_email' => $audit->user?->email,
            'old_values' => $this->formatValues($audit->old_values),
            'new_values' => $this->formatValues($audit->new_values),
            'url' => $audit->url,
            'ip_address' => $audit->ip_address,
        ];
    }

    public function userFilterOptions(): array
    {
        $userIds = Audit::query()
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        if ($userIds->isEmpty()) {
            return [];
        }

        return User::query()
            ->whereIn('id', $userIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
            ])
            ->all();
    }

    private function formatValues($values): ?string
    {
        if ($values === null || $values === [] || $values === '') {
            return null;
        }

        if (is_string($values)) {
            $decoded = json_decode($values, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $values = $decoded;
            } else {
                return $values;
            }
        }

        return json_encode($values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
