<?php

namespace App\Modules\Audit\Application;

use App\Models\User;
use App\Modules\Audit\Domain\Models\AuditEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecordAuditEvent
{
    public function execute(
        string $action,
        Model $resource,
        ?User $actor = null,
        ?array $before = null,
        ?array $after = null,
        ?string $authorizationRequestId = null,
    ): AuditEvent {
        $request = app()->bound('request') ? app(Request::class) : null;

        return AuditEvent::create([
            'actor_user_id' => $actor?->getKey() ?? $request?->user()?->getKey(),
            'action' => $action,
            'resource_type' => $resource->getMorphClass(),
            'resource_id' => (string) $resource->getKey(),
            'authorization_request_id' => $authorizationRequestId,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request?->ip(),
            'correlation_id' => $request?->attributes->get('correlation_id') ?? (string) Str::ulid(),
        ]);
    }
}
