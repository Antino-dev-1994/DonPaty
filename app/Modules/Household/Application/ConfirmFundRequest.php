<?php

namespace App\Modules\Household\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Household\Domain\Enums\FundRequestStatus;
use App\Modules\Household\Domain\Models\FundRequest;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class ConfirmFundRequest
{
    public function __construct(
        private readonly RecordFundRequestHistory $history,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(FundRequest $request, User $actor): FundRequest
    {
        if ($actor->person_id !== $request->requester_person_id && ! $actor->hasPermission('household.view-all')) {
            throw new AuthorizationException('Solo el solicitante o el propietario puede confirmar la recepción.');
        }

        return DB::transaction(function () use ($request, $actor): FundRequest {
            $request = FundRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($request->status !== FundRequestStatus::Paid) {
                throw new DomainException('Solo se puede confirmar una solicitud pagada.');
            }
            $before = $request->toArray();
            $request->update(['status' => FundRequestStatus::Confirmed, 'confirmed_by' => $actor->id, 'confirmed_at' => now()]);
            $this->history->execute($request, FundRequestStatus::Paid, FundRequestStatus::Confirmed, $actor);
            $this->audit->execute('household.fund_request_confirmed', $request, $actor, $before, $request->fresh()->toArray());

            return $request->fresh();
        });
    }
}
