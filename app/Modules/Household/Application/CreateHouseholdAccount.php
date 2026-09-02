<?php

namespace App\Modules\Household\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Finance\Application\Data\JournalEntryData;
use App\Modules\Finance\Application\Data\JournalLineData;
use App\Modules\Finance\Application\PostJournalEntry;
use App\Modules\Finance\Domain\Enums\FinancialAccountType;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Household\Application\Data\CreateHouseholdAccountData;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class CreateHouseholdAccount
{
    public function __construct(
        private readonly NextDocumentNumber $numbers,
        private readonly PostJournalEntry $journal,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(CreateHouseholdAccountData $data): FinancialAccount
    {
        if (! in_array($data->scope, [FinancialScope::Household, FinancialScope::Personal], true)) {
            throw new DomainException('La cuenta debe pertenecer al hogar o a una persona.');
        }
        if ($data->scope === FinancialScope::Personal && ! $data->personId) {
            throw new DomainException('Una cuenta personal requiere habitante.');
        }
        if ($data->openingBalance < 0 || trim($data->name) === '') {
            throw new DomainException('El nombre es obligatorio y el saldo inicial no puede ser negativo.');
        }

        return DB::transaction(function () use ($data): FinancialAccount {
            $account = FinancialAccount::create([
                'code' => $this->numbers->execute('household_account', 'CTA', now()),
                'name' => trim($data->name),
                'account_type' => FinancialAccountType::Asset,
                'scope' => $data->scope,
                'person_id' => $data->scope === FinancialScope::Personal ? $data->personId : null,
                'accepts_payments' => true,
                'is_active' => true,
            ]);
            if ($data->openingBalance > 0) {
                $this->journal->execute(new JournalEntryData(
                    now(),
                    "Saldo inicial de {$account->name}",
                    $data->creator,
                    [
                        new JournalLineData($account->id, $data->openingBalance, 0, $account->person_id, 'Saldo disponible'),
                        new JournalLineData(FinancialAccount::query()->where('code', '3105-HOG-PATRIMONIO')->sole()->id, 0, $data->openingBalance, $account->person_id, 'Patrimonio inicial'),
                    ],
                    $account,
                ));
            }
            $this->audit->execute('household.account_created', $account, $data->creator, after: $account->toArray());

            return $account;
        });
    }
}
