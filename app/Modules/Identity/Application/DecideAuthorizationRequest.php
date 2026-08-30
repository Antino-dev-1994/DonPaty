<?php

namespace App\Modules\Identity\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Identity\Domain\Enums\AuthorizationStatus;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use DomainException;

class DecideAuthorizationRequest
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(AuthorizationRequest $authorization, User $approver, AuthorizationStatus $decision, ?string $notes): void
    {
        if ($authorization->status !== AuthorizationStatus::Pending) {
            throw new DomainException('La solicitud ya fue decidida.');
        }

        if ($authorization->requested_by === $approver->getKey()) {
            throw new DomainException('No puedes autorizar tu propia solicitud.');
        }

        if (! $approver->hasPermission($authorization->approval_permission)) {
            throw new DomainException('No tienes el permiso requerido para esta autorización.');
        }

        if (! in_array($decision, [AuthorizationStatus::Approved, AuthorizationStatus::Rejected], true)) {
            throw new DomainException('La decisión indicada no es válida.');
        }

        $authorization->update([
            'approved_by' => $approver->getKey(),
            'decision_notes' => $notes,
            'status' => $decision,
            'decided_at' => now(),
        ]);
        $this->audit->execute('authorization.'.$decision->value, $authorization, $approver, after: $authorization->toArray());
    }
}
