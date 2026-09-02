<?php

namespace App\Modules\Household\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Household\Application\Data\CreateFundRequestData;
use App\Modules\Household\Domain\Enums\FundRequestStatus;
use App\Modules\Household\Domain\Models\FundRequest;
use App\Modules\People\Domain\Models\Person;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class CreateFundRequest
{
    public function __construct(
        private readonly NextDocumentNumber $numbers,
        private readonly RecordFundRequestHistory $history,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(CreateFundRequestData $data): FundRequest
    {
        if (! in_array($data->sourceScope, [FinancialScope::Household, FinancialScope::Business], true)) {
            throw new DomainException('La solicitud debe dirigirse al hogar o al negocio.');
        }
        if ($data->amount <= 0 || trim($data->reason) === '') {
            throw new DomainException('La solicitud requiere valor positivo y motivo.');
        }
        Person::query()->where('is_active', true)->findOrFail($data->requesterPersonId);

        return DB::transaction(function () use ($data): FundRequest {
            $request = FundRequest::create([
                'document_number' => $this->numbers->execute('fund_request', 'SOL', now()),
                'requester_person_id' => $data->requesterPersonId,
                'source_scope' => $data->sourceScope,
                'amount' => $data->amount,
                'reason' => trim($data->reason),
                'needed_at' => $data->neededAt,
                'status' => FundRequestStatus::Requested,
                'created_by' => $data->creator->id,
            ]);
            $this->history->execute($request, null, FundRequestStatus::Requested, $data->creator);
            $this->audit->execute('household.fund_request_created', $request, $data->creator, after: $request->toArray());

            return $request;
        });
    }
}
