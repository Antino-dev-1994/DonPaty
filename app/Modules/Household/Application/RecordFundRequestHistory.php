<?php

namespace App\Modules\Household\Application;

use App\Models\User;
use App\Modules\Household\Domain\Enums\FundRequestStatus;
use App\Modules\Household\Domain\Models\FundRequest;

class RecordFundRequestHistory
{
    public function execute(FundRequest $request, ?FundRequestStatus $from, FundRequestStatus $to, User $actor, ?string $notes = null): void
    {
        $request->histories()->create([
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'changed_by' => $actor->id,
            'changed_at' => now(),
            'notes' => $notes,
        ]);
    }
}
