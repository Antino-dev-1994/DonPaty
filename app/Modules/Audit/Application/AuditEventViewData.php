<?php

namespace App\Modules\Audit\Application;

use App\Modules\Audit\Domain\Models\AuditEvent;

class AuditEventViewData
{
    public function execute(AuditEvent $event): array
    {
        $authorization = $event->authorizationRequest;

        return [
            'id' => $event->id,
            'actor' => $event->actor?->name ?? 'Sistema',
            'action' => $event->action,
            'resource_type' => class_basename($event->resource_type),
            'resource_id' => $event->resource_id,
            'ip_address' => $event->ip_address,
            'correlation_id' => $event->correlation_id,
            'before_data' => $event->before_data,
            'after_data' => $event->after_data,
            'authorization' => $authorization ? [
                'id' => $authorization->id,
                'operation_type' => $authorization->operation_type,
                'status' => $authorization->status->label(),
                'reason' => $authorization->reason,
                'requester' => $authorization->requester?->name,
                'approver' => $authorization->approver?->name,
                'url' => route('authorizations.index', ['authorization' => $authorization->id]),
            ] : null,
            'created_at' => $event->created_at->timezone(config('regional.display_timezone'))->format('Y-m-d H:i:s'),
        ];
    }
}
