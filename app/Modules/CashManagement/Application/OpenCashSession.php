<?php

namespace App\Modules\CashManagement\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\CashManagement\Domain\Enums\CashSessionStatus;
use App\Modules\CashManagement\Domain\Models\CashSession;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use DomainException;
use Illuminate\Support\Facades\DB;

class OpenCashSession
{
    public function __construct(
        private readonly TransferCash $transfer,
        private readonly FinancialAccountBalance $balances,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(string $accountId, int $openingAmount, User $actor, ?string $sourceAccountId = null): CashSession
    {
        if ($openingAmount < 0) throw new DomainException('La base inicial no puede ser negativa.');

        return DB::transaction(function () use ($accountId, $openingAmount, $actor, $sourceAccountId): CashSession {
            FinancialAccount::query()->where('accepts_payments', true)->where('is_active', true)->findOrFail($accountId);
            if (CashSession::query()->where('financial_account_id', $accountId)->where('status', CashSessionStatus::Open)->lockForUpdate()->exists()) throw new DomainException('La caja ya tiene una sesión abierta.');
            if ($openingAmount > 0 && $sourceAccountId) {
                $this->transfer->execute($sourceAccountId, $accountId, $openingAmount, now(), 'Base inicial de caja menor.', $actor);
            } elseif ($openingAmount > $this->balances->execute($accountId)) {
                throw new DomainException('La base declarada supera el saldo contable de la caja. Registra el saldo inicial o transfiérelo desde otra cuenta.');
            }
            $session = CashSession::create(['financial_account_id' => $accountId, 'opened_by' => $actor->id, 'opened_at' => now(), 'opening_amount' => $openingAmount, 'status' => CashSessionStatus::Open]);
            $this->audit->execute('cash.session_opened', $session, $actor, after: $session->toArray());
            return $session;
        });
    }
}
