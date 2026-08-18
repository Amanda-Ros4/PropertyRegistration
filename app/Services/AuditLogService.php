<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Support\SearchInput;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditLogService
{
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $search = SearchInput::sanitize($filters['search'] ?? null);

        return AuditLog::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('user_name', 'like', "%{$search}%")
                        ->orWhere('user_email', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}
