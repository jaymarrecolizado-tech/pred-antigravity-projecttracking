<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Access\GrantResolver;
use App\Domain\Audit\AuditLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditController extends Controller
{
    public function __construct(
        protected GrantResolver $grantResolver,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        if (!$this->grantResolver->allows($user, '*')) {
            abort(403, 'Unauthorized to view immutable audit trail.');
        }

        $logs = AuditLog::with('user')
            ->latest('id')
            ->paginate(20)
            ->through(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'user_name' => $log->user?->name ?? 'System',
                    'user_email' => $log->user?->email ?? 'N/A',
                    'auditable_type' => class_basename($log->auditable_type ?? ''),
                    'auditable_id' => $log->auditable_id,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'before_state' => $log->before_state,
                    'after_state' => $log->after_state,
                    'created_at' => $log->created_at?->toDateTimeString(),
                ];
            });

        return Inertia::render('Admin/Audit/Index', [
            'logs' => $logs,
        ]);
    }
}
