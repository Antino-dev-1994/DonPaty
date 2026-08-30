<?php

namespace App\Modules\Identity\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use DomainException;

class UseAuthorization
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(AuthorizationRequest $authorization): void
    {
        if (! $authorization->isUsable()) {
            throw new DomainException('La autorización no está vigente o ya fue utilizada.');
        }

        $authorization->update(['used_at' => now()]);
        $this->audit->execute('authorization.used', $authorization, authorizationRequestId: $authorization->id);
    }
}
