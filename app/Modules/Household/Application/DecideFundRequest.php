<?php

namespace App\Modules\Household\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Household\Domain\Enums\FundRequestStatus;
use App\Modules\Household\Domain\Models\FundRequest;
use DomainException;
use Illuminate\Support\Facades\DB;

class DecideFundRequest
{
    public function __construct(
        private readonly RecordFundRequestHistory $history,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(FundRequest $request, FundRequestStatus $decision, User $actor, ?string $notes = null): FundRequest
    {
        if (! in_array($decision, [FundRequestStatus::Approved, FundRequestStatus::Rejected], true)) {
            throw new DomainException('La decisión indicada no es válida.');
        }

        return DB::transaction(function () use ($request, $decision, $actor, $notes): FundRequest {
            $request = FundRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($request->status !== FundRequestStatus::Requested) {
                throw new DomainException('Solo se puede decidir una solicitud pendiente.');
            }
            $selfApproval = $actor->person_id === $request->requester_person_id;
            if ($decision === FundRequestStatus::Approved && $selfApproval && (! $actor->hasRole('owner') || ! config('business.owner_can_self_approve_fund_requests'))) {
                throw new DomainException('El solicitante no puede aprobar su propia solicitud.');
            }

            $before = $request->toArray();
            $request->update($decision === FundRequestStatus::Approved ? [
                'status' => $decision,
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'decision_notes' => $notes,
            ] : [
                'status' => $decision,
                'rejected_by' => $actor->id,
                'rejected_at' => now(),
                'decision_notes' => $notes,
            ]);
            $this->history->execute($request, FundRequestStatus::Requested, $decision, $actor, $notes);
            $this->audit->execute('household.fund_request_decided', $request, $actor, $before, $request->fresh()->toArray());

            return $request->fresh();
        });
    }
}
