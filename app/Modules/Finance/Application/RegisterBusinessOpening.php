<?php

namespace App\Modules\Finance\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Finance\Application\Data\JournalEntryData;
use App\Modules\Finance\Application\Data\JournalLineData;
use App\Modules\Finance\Application\Data\RegisterBusinessOpeningData;
use App\Modules\Finance\Domain\Models\BusinessOpening;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use DomainException;
use Illuminate\Support\Facades\DB;

class RegisterBusinessOpening
{
    public function __construct(
        private readonly PostJournalEntry $journal,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(RegisterBusinessOpeningData $data): BusinessOpening
    {
        if ($data->cashAmount < 0 || $data->nequiAmount < 0 || $data->cashAmount + $data->nequiAmount <= 0) {
            throw new DomainException('La apertura debe tener al menos un saldo positivo y no admite valores negativos.');
        }

        return DB::transaction(function () use ($data): BusinessOpening {
            if (BusinessOpening::query()->whereDate('opened_on', $data->openedOn)->lockForUpdate()->exists()) {
                throw new DomainException('Ya existe una apertura del negocio para esta fecha.');
            }

            $cash = $this->account('1105-CAJA');
            $nequi = $this->account('1110-NEQUI');
            $capital = $this->account('3105-CAPITAL-APORTADO');
            $opening = BusinessOpening::create([
                'opened_on' => $data->openedOn->toDateString(),
                'cash_amount' => $data->cashAmount,
                'nequi_amount' => $data->nequiAmount,
                'notes' => $data->notes,
                'opened_by' => $data->opener->id,
            ]);
            $total = $data->cashAmount + $data->nequiAmount;
            $entry = $this->journal->execute(new JournalEntryData(
                $data->openedOn,
                'Apertura inicial del negocio.',
                $data->opener,
                [
                    new JournalLineData($cash->id, $data->cashAmount, 0, null, 'Saldo inicial en efectivo'),
                    new JournalLineData($nequi->id, $data->nequiAmount, 0, null, 'Saldo inicial en Nequi'),
                    new JournalLineData($capital->id, 0, $total, null, 'Capital aportado al negocio'),
                ],
                $opening,
            ));
            $opening->update(['journal_entry_id' => $entry->id]);
            $this->audit->execute('finance.business_opening_registered', $opening, $data->opener, after: $opening->fresh()->toArray());

            return $opening->fresh(['journalEntry', 'opener']);
        });
    }

    private function account(string $code): FinancialAccount
    {
        return FinancialAccount::query()->where('code', $code)->where('is_active', true)->sole();
    }
}
