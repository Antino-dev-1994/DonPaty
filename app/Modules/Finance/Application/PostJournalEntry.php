<?php

namespace App\Modules\Finance\Application;

use App\Modules\Finance\Application\Data\JournalEntryData;
use App\Modules\Finance\Domain\Enums\FinancialDocumentStatus;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\JournalEntry;
use App\Modules\Shared\Application\NextDocumentNumber;
use DomainException;
use Illuminate\Support\Facades\DB;

class PostJournalEntry
{
    public function __construct(private readonly NextDocumentNumber $nextDocumentNumber) {}

    public function execute(JournalEntryData $data): JournalEntry
    {
        $debits = array_sum(array_map(fn ($line) => $line->debit, $data->lines));
        $credits = array_sum(array_map(fn ($line) => $line->credit, $data->lines));
        if ($data->lines === [] || $debits <= 0 || $debits !== $credits) {
            throw new DomainException('El asiento debe tener débitos y créditos iguales y positivos.');
        }

        return DB::transaction(function () use ($data): JournalEntry {
            $entry = JournalEntry::create([
                'document_number' => $this->nextDocumentNumber->execute('journal_entry', 'ASI', $data->effectiveAt),
                'effective_at' => $data->effectiveAt, 'description' => $data->description,
                'status' => FinancialDocumentStatus::Confirmed, 'source_type' => $data->source?->getMorphClass(),
                'source_id' => $data->source ? (string) $data->source->getKey() : null, 'posted_by' => $data->poster->id,
            ]);
            foreach ($data->lines as $line) {
                FinancialAccount::query()->where('is_active', true)->findOrFail($line->accountId);
                if (($line->debit > 0) === ($line->credit > 0)) {
                    throw new DomainException('Cada línea contable debe tener débito o crédito, nunca ambos.');
                }
                $entry->lines()->create([
                    'financial_account_id' => $line->accountId, 'debit_amount' => $line->debit,
                    'credit_amount' => $line->credit, 'person_id' => $line->personId, 'description' => $line->description,
                ]);
            }

            return $entry->load('lines');
        });
    }
}
