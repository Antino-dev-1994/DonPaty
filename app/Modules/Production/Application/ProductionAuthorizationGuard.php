<?php

namespace App\Modules\Production\Application;

use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Production\Domain\Models\ProductionOrder;
use DomainException;

class ProductionAuthorizationGuard
{
    public function ensureUsableFor(
        ?AuthorizationRequest $authorization,
        ProductionOrder $order,
        string $permission,
    ): AuthorizationRequest {
        if (
            ! $authorization?->isUsable()
            || $authorization->approval_permission !== $permission
            || $authorization->resource_type !== $order->getMorphClass()
            || (string) $authorization->resource_id !== (string) $order->getKey()
        ) {
            throw new DomainException('La autorización no es válida para esta producción.');
        }

        return $authorization;
    }
}
