<?php

namespace App\Modules\Audit\Application;

use App\Modules\Audit\Application\Data\AuditFilters;
use App\Modules\Audit\Domain\Models\AuditEvent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAuditEvents
{
    public function execute(AuditFilters $filters): LengthAwarePaginator
    {
        return AuditEvent::query()
            ->with(['actor:id,name', 'authorizationRequest.requester:id,name', 'authorizationRequest.approver:id,name'])
            ->when($filters->search !== '', fn ($query) => $query->where(function ($query) use ($filters): void {
                $query->where('action', 'like', "%{$filters->search}%")
                    ->orWhere('resource_type', 'like', "%{$filters->search}%")
                    ->orWhere('resource_id', 'like', "%{$filters->search}%")
                    ->orWhere('correlation_id', 'like', "%{$filters->search}%");
            }))
            ->when($filters->actorUserId, fn ($query, int $actor) => $query->where('actor_user_id', $actor))
            ->when($filters->action !== '', fn ($query) => $query->where('action', 'like', "%{$filters->action}%"))
            ->when($filters->resourceId !== '', fn ($query) => $query->where('resource_id', 'like', "%{$filters->resourceId}%"))
            ->when($filters->dateFrom, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters->dateTo, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters->authorization === 'with', fn ($query) => $query->whereNotNull('authorization_request_id'))
            ->when($filters->authorization === 'without', fn ($query) => $query->whereNull('authorization_request_id'))
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();
    }
}
