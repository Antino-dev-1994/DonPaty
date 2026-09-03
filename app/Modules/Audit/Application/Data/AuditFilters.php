<?php

namespace App\Modules\Audit\Application\Data;

use App\Modules\Audit\Presentation\Http\Requests\ListAuditEventsRequest;

readonly class AuditFilters
{
    public function __construct(
        public string $search,
        public ?int $actorUserId,
        public string $action,
        public string $resourceId,
        public ?string $dateFrom,
        public ?string $dateTo,
        public string $authorization,
    ) {}

    public static function fromRequest(ListAuditEventsRequest $request): self
    {
        return new self(
            search: trim((string) $request->validated('search', '')),
            actorUserId: $request->filled('actor_user_id') ? (int) $request->validated('actor_user_id') : null,
            action: trim((string) $request->validated('action', '')),
            resourceId: trim((string) $request->validated('resource_id', '')),
            dateFrom: $request->validated('date_from'),
            dateTo: $request->validated('date_to'),
            authorization: (string) $request->validated('authorization', 'any'),
        );
    }

    public function toArray(): array
    {
        return [
            'search' => $this->search,
            'actor_user_id' => $this->actorUserId,
            'action' => $this->action,
            'resource_id' => $this->resourceId,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'authorization' => $this->authorization,
        ];
    }
}
