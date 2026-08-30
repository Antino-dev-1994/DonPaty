<?php

namespace App\Modules\Identity\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Identity\Domain\Enums\AuthorizationStatus;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use Illuminate\Database\Eloquent\Model;

class CreateAuthorizationRequest
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(string $operationType, string $approvalPermission, Model $resource, User $requester, string $reason, ?int $expiresInMinutes = 60): AuthorizationRequest
    {
        $authorization = AuthorizationRequest::create([
            'operation_type' => $operationType,
            'approval_permission' => $approvalPermission,
            'resource_type' => $resource->getMorphClass(),
            'resource_id' => (string) $resource->getKey(),
            'requested_by' => $requester->getKey(),
            'reason' => $reason,
            'status' => AuthorizationStatus::Pending,
            'expires_at' => $expiresInMinutes === null ? null : now()->addMinutes($expiresInMinutes),
        ]);
        $this->audit->execute('authorization.requested', $authorization, $requester, after: $authorization->toArray());

        return $authorization;
    }
}
