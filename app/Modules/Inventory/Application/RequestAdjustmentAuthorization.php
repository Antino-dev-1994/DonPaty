<?php

namespace App\Modules\Inventory\Application;

use App\Models\User;
use App\Modules\Identity\Application\CreateAuthorizationRequest;
use App\Modules\Identity\Domain\Enums\AuthorizationStatus;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Inventory\Domain\Models\InventoryAdjustment;
use DomainException;

class RequestAdjustmentAuthorization
{
    public function __construct(private readonly CreateAuthorizationRequest $createAuthorization) {}

    public function execute(InventoryAdjustment $adjustment, User $requester, string $reason): AuthorizationRequest
    {
        $adjustment->loadMissing(['lines.presentation.item']);
        $hasNegative = $adjustment->lines->contains(function ($line): bool {
            return bccomp($line->counted_quantity, '0', 6) < 0
                && $line->presentation->item->allow_negative_stock;
        });
        if (! $hasNegative) {
            throw new DomainException('Este ajuste no requiere autorización por inventario negativo.');
        }

        $existing = AuthorizationRequest::query()
            ->where('resource_type', $adjustment->getMorphClass())
            ->where('resource_id', $adjustment->id)
            ->whereIn('status', [AuthorizationStatus::Pending, AuthorizationStatus::Approved])
            ->whereNull('used_at')
            ->latest()
            ->first();

        return $existing ?? $this->createAuthorization->execute(
            'inventory.negative-adjustment',
            'inventory.authorize-negative',
            $adjustment,
            $requester,
            $reason,
        );
    }
}
