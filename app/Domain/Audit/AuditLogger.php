<?php

namespace App\Domain\Audit;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Record an immutable audit log entry.
     */
    public static function log(
        string $action,
        ?Model $auditable = null,
        ?array $before = null,
        ?array $after = null,
        ?User $user = null
    ): AuditLog {
        $currentUser = $user ?? Auth::user();

        return AuditLog::create([
            'user_id' => $currentUser?->id,
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->getKey(),
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent() ?? 'System',
            'before_state' => $before,
            'after_state' => $after,
            'created_at' => now(),
        ]);
    }
}
