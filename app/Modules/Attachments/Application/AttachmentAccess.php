<?php

namespace App\Modules\Attachments\Application;

use App\Models\User;
use App\Modules\Household\Application\HouseholdVisibility;
use App\Modules\Household\Domain\Models\FundRequest;
use App\Modules\Household\Domain\Models\HouseholdTransaction;
use Illuminate\Database\Eloquent\Model;

class AttachmentAccess
{
    public function __construct(private readonly HouseholdVisibility $householdVisibility) {}

    public function canView(User $user, string $type, Model $resource): bool
    {
        return match ($type) {
            'sale' => $user->hasPermission('sales.create') || $user->hasPermission('finance.view'),
            'purchase' => $user->hasPermission('purchases.manage') || $user->hasPermission('purchases.receive'),
            'production' => $user->hasPermission('production.view'),
            'order' => $user->hasPermission('orders.view'),
            'expense', 'income' => $user->hasPermission('finance.view'),
            'fund-request' => $resource instanceof FundRequest && $this->householdVisibility->fundRequests(FundRequest::query()->whereKey($resource), $user)->exists(),
            'household-transaction' => $resource instanceof HouseholdTransaction && $this->householdVisibility->transactions(HouseholdTransaction::query()->whereKey($resource), $user)->exists(),
            default => false,
        };
    }

    public function canUpload(User $user, string $type, Model $resource): bool
    {
        if (! $this->canView($user, $type, $resource)) {
            return false;
        }

        return match ($type) {
            'sale' => $user->hasPermission('sales.create'),
            'purchase' => $user->hasPermission('purchases.manage') || $user->hasPermission('purchases.receive'),
            'production' => $user->hasPermission('production.manage'),
            'order' => $user->hasPermission('orders.manage'),
            'expense', 'income' => $user->hasPermission('finance.manage'),
            'fund-request' => $user->hasPermission('household.manage') || $user->person_id === $resource->requester_person_id,
            'household-transaction' => $user->hasPermission('household.manage'),
            default => false,
        };
    }

    public function canDelete(User $user, string $type, Model $resource, int $uploaderId): bool
    {
        return $this->canUpload($user, $type, $resource) && ($user->id === $uploaderId || $user->hasRole('owner'));
    }
}
