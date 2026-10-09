<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an audit event.
     */
    public static function log(
        string $event,
        ?Model $entity = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $tenantId = null,
        ?int $branchId = null
    ): AuditLog {
        $user = Auth::user();
        
        $tenantId = $tenantId ?? ($user?->tenant_id ?? ($entity && isset($entity->tenant_id) ? $entity->tenant_id : null));
        $branchId = $branchId ?? ($user?->branch_id ?? ($entity && isset($entity->branch_id) ? $entity->branch_id : null));

        return AuditLog::create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'user_id' => $user?->id,
            'event' => $event,
            'entity_type' => $entity ? get_class($entity) : null,
            'entity_id' => $entity?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
