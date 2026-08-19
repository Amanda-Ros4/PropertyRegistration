<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Services\AuditService;
use App\Support\AuditableTypes;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditController extends Controller
{
    public function __construct(private readonly AuditService $auditService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Audit::class);

        $filters = [
            'user_id' => $request->filled('user_id') ? (int) $request->input('user_id') : null,
            'event' => $request->input('event'),
            'date' => $request->input('date'),
            'auditable_type' => $request->input('auditable_type'),
        ];

        return Inertia::render('Audit/Index', [
            'logs' => $this->auditService->list($filters),
            'filters' => $filters,
            'filterOptions' => [
                'users' => $this->auditService->userFilterOptions(),
                'events' => [
                    ['value' => 'created', 'label_key' => 'audit.events.created'],
                    ['value' => 'updated', 'label_key' => 'audit.events.updated'],
                    ['value' => 'deleted', 'label_key' => 'audit.events.deleted'],
                ],
                'tables' => AuditableTypes::options(),
            ],
        ]);
    }

    public function show(Audit $audit): Response
    {
        $this->authorize('view', $audit);

        $audit->load('user:id,name,email');

        return Inertia::render('Audit/Show', [
            'audit' => $this->auditService->transformForShow($audit),
        ]);
    }
}
