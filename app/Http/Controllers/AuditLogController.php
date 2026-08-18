<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\AuditLogService;
use App\Support\SearchInput;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AuditLog::class);

        $filters = [
            'search' => SearchInput::sanitize($request->input('search')),
        ];

        return Inertia::render('Audit/Index', [
            'logs' => $this->auditLogService->list($filters),
            'filters' => $filters,
        ]);
    }
}
