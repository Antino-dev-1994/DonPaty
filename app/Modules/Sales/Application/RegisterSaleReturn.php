<?php

namespace App\Modules\Sales\Application;

use App\Models\User;
use App\Modules\CashManagement\Application\FinancialAccountBalance;
use App\Modules\CashManagement\Domain\Enums\CashSessionStatus;
use App\Modules\CashManagement\Domain\Models\CashSession;
use App\Modules\Finance\Application\Data\JournalEntryData;
use App\Modules\Finance\Application\Data\JournalLineData;
use App\Modules\Finance\Application\PostJournalEntry;
use App\Modules\Finance\Domain\Enums\FinancialDocumentStatus;
use App\Modules\Finance\Domain\Enums\PaymentDirection;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\Inventory\Application\Data\InventoryMovementData;
use App\Modules\Inventory\Application\Data\InventoryMovementLineData;
use App\Modules\Inventory\Application\PostInventoryMovement;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\Sales\Application\Data\SaleReturnLineData;
use App\Modules\Sales\Domain\Enums\ReceivableStatus;
use App\Modules\Sales\Domain\Enums\SaleStatus;
use App\Modules\Sales\Domain\Models\Sale;
use App\Modules\Sales\Domain\Models\SaleReturn;
use App\Modules\Sales\Domain\Models\SaleReturnLine;
use App\Modules\Shared\Application\NextDocumentNumber;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RegisterSaleReturn
{
    public function __construct(
        private readonly NextDocumentNumber $numbers,
        private readonly PostInventoryMovement $postInventoryMovement,
        private readonly PostJournalEntry $postJournalEntry,
        private readonly FinancialAccountBalance $accountBalances,
    ) {}

    /** @param array<int, SaleReturnLineData> $requestedLines */
    public function execute(
        Sale $sale,
        array $requestedLines,
        CarbonInterface $returnedAt,
        string $reason,
        User $actor,
        ?string $refundAccountId = null,
    ): SaleReturn {
        if (trim($reason) === '') {
            throw new DomainException('La devolución requiere un motivo.');
        }

        return DB::transaction(function () use ($sale, $requestedLines, $returnedAt, $reason, $actor, $refundAccountId): SaleReturn {
            $sale = Sale::query()
                ->with(['lines.presentation', 'receivable'])
                ->lockForUpdate()
                ->findOrFail($sale->id);

            if (in_array($sale->status, [SaleStatus::Draft, SaleStatus::Reversed], true)) {
                throw new DomainException('Solo pueden devolverse ventas confirmadas y vigentes.');
            }

            $lines = $this->calculateLines($sale, $requestedLines);
            if ($lines->isEmpty()) {
                throw new DomainException('Debes indicar al menos un producto para devolver.');
            }

            $totalRefund = (int) $lines->sum('refund_amount');
            $totalCost = (int) $lines->where('returns_to_inventory', true)->sum('cost_amount');
            $saleReturn = SaleReturn::create([
                'document_number' => $this->numbers->execute('sale_return', 'DEV', $returnedAt),
                'sale_id' => $sale->id,
                'returned_at' => $returnedAt,
                'status' => FinancialDocumentStatus::Confirmed,
                'total_refund' => $totalRefund,
                'total_cost' => $totalCost,
                'reason' => trim($reason),
                'created_by' => $actor->id,
            ]);

            foreach ($lines as $line) {
                $saleReturn->lines()->create([
                    'sale_line_id' => $line['sale_line_id'],
                    'quantity' => $line['quantity'],
                    'returns_to_inventory' => $line['returns_to_inventory'],
                    'refund_amount' => $line['refund_amount'],
                    'cost_amount' => $line['cost_amount'],
                    'condition_notes' => $line['condition_notes'],
                ]);
            }

            $movement = $this->restoreInventory($saleReturn, $lines, $returnedAt, $actor);
            [$creditApplied, $cashRefund, $refundAccount] = $this->settleRefund(
                $sale,
                $saleReturn,
                $totalRefund,
                $refundAccountId,
                $returnedAt,
                $actor,
            );
            $entry = $this->postAccountingEntry(
                $sale,
                $saleReturn,
                $returnedAt,
                $actor,
                $totalRefund,
                $totalCost,
                $creditApplied,
                $cashRefund,
                $refundAccount,
            );

            $saleReturn->update([
                'inventory_movement_id' => $movement?->id,
                'journal_entry_id' => $entry->id,
            ]);

            return $saleReturn->fresh(['lines.saleLine.presentation.item', 'inventoryMovement', 'journalEntry']);
        });
    }

    /**
     * @param array<int, SaleReturnLineData> $requestedLines
     * @return Collection<int, array<string, mixed>>
     */
    private function calculateLines(Sale $sale, array $requestedLines): Collection
    {
        $duplicates = collect($requestedLines)
            ->groupBy(fn (SaleReturnLineData $line) => $line->saleLineId)
            ->filter(fn (Collection $group) => $group->count() > 1);
        if ($duplicates->isNotEmpty()) {
            throw new DomainException('Un producto no puede aparecer dos veces en la misma devolución.');
        }

        $prior = SaleReturnLine::query()
            ->selectRaw('sale_return_lines.sale_line_id, SUM(sale_return_lines.quantity) as quantity, SUM(sale_return_lines.refund_amount) as refund_amount')
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_lines.sale_return_id')
            ->where('sale_returns.sale_id', $sale->id)
            ->where('sale_returns.status', FinancialDocumentStatus::Confirmed)
            ->groupBy('sale_return_lines.sale_line_id')
            ->get()
            ->keyBy('sale_line_id');

        return collect($requestedLines)->map(function (SaleReturnLineData $requested) use ($sale, $prior): array {
            $saleLine = $sale->lines->firstWhere('id', $requested->saleLineId);
            if (! $saleLine || bccomp($requested->quantity, '0', 6) <= 0) {
                throw new DomainException('La cantidad devuelta debe ser positiva y pertenecer a la venta.');
            }

            $previousQuantity = (string) ($prior[$saleLine->id]?->quantity ?? '0');
            $remainingQuantity = bcsub($saleLine->quantity, $previousQuantity, 6);
            if (bccomp($requested->quantity, $remainingQuantity, 6) > 0) {
                throw new DomainException("La devolución supera lo vendido para {$saleLine->presentation->name}.");
            }

            $previousRefund = (int) ($prior[$saleLine->id]?->refund_amount ?? 0);
            $isFinalReturn = bccomp($requested->quantity, $remainingQuantity, 6) === 0;
            $refundAmount = $isFinalReturn
                ? $saleLine->line_total - $previousRefund
                : (int) round(($saleLine->line_total / (float) $saleLine->quantity) * (float) $requested->quantity);
            $costAmount = (int) round((int) $saleLine->unit_cost * (float) $requested->quantity);

            return [
                'sale_line_id' => $saleLine->id,
                'quantity' => $requested->quantity,
                'returns_to_inventory' => $requested->returnsToInventory,
                'refund_amount' => $refundAmount,
                'cost_amount' => $costAmount,
                'condition_notes' => $requested->conditionNotes,
                'presentation_id' => $saleLine->presentation_id,
                'unit_cost' => (int) $saleLine->unit_cost,
            ];
        });
    }

    private function restoreInventory(SaleReturn $saleReturn, Collection $lines, CarbonInterface $returnedAt, User $actor)
    {
        $inventoryLines = $lines
            ->where('returns_to_inventory', true)
            ->map(fn (array $line) => InventoryMovementLineData::incoming(
                $line['presentation_id'],
                $line['quantity'],
                $line['unit_cost'],
                $line['cost_amount'],
            ))
            ->values()
            ->all();

        if ($inventoryLines === []) {
            return null;
        }

        return $this->postInventoryMovement->execute(new InventoryMovementData(
            InventoryMovementType::SaleReturn,
            $returnedAt,
            $actor,
            $inventoryLines,
            $saleReturn,
            "Devolución {$saleReturn->document_number}",
        ));
    }

    /** @return array{int, int, ?FinancialAccount} */
    private function settleRefund(
        Sale $sale,
        SaleReturn $saleReturn,
        int $totalRefund,
        ?string $refundAccountId,
        CarbonInterface $returnedAt,
        User $actor,
    ): array {
        $receivable = $sale->receivable;
        $creditApplied = min($totalRefund, (int) ($receivable?->balance_amount ?? 0));
        if ($receivable && $creditApplied > 0) {
            $newBalance = $receivable->balance_amount - $creditApplied;
            $receivable->update([
                'credited_amount' => $receivable->credited_amount + $creditApplied,
                'balance_amount' => $newBalance,
                'status' => $newBalance === 0 ? ReceivableStatus::Paid : ReceivableStatus::Partial,
            ]);
            $sale->update(['balance_amount' => $newBalance]);
        }

        $cashRefund = $totalRefund - $creditApplied;
        if ($cashRefund === 0) {
            return [$creditApplied, 0, null];
        }

        if (! $refundAccountId) {
            throw new DomainException('Debes indicar la cuenta desde la que se reembolsará el dinero.');
        }

        $account = FinancialAccount::query()
            ->where('accepts_payments', true)
            ->where('is_active', true)
            ->findOrFail($refundAccountId);
        if ($account->code === '1105-CAJA-MENOR' && ! CashSession::query()
            ->where('financial_account_id', $account->id)
            ->where('status', CashSessionStatus::Open)
            ->exists()) {
            throw new DomainException('Debes abrir la caja menor antes de realizar el reembolso.');
        }
        if ($this->accountBalances->execute($account->id) < $cashRefund) {
            throw new DomainException('La cuenta seleccionada no tiene saldo suficiente para el reembolso.');
        }

        $payment = Payment::create([
            'document_number' => $this->numbers->execute('sale_refund', 'REE', $returnedAt),
            'direction' => PaymentDirection::Outgoing,
            'person_id' => $sale->customer_person_id,
            'paid_at' => $returnedAt,
            'amount' => $cashRefund,
            'financial_account_id' => $account->id,
            'status' => FinancialDocumentStatus::Confirmed,
            'reference' => "Reembolso {$saleReturn->document_number}",
            'created_by' => $actor->id,
        ]);
        $payment->allocations()->create([
            'allocatable_type' => $saleReturn->getMorphClass(),
            'allocatable_id' => $saleReturn->id,
            'amount' => $cashRefund,
        ]);

        return [$creditApplied, $cashRefund, $account];
    }

    private function postAccountingEntry(
        Sale $sale,
        SaleReturn $saleReturn,
        CarbonInterface $returnedAt,
        User $actor,
        int $totalRefund,
        int $totalCost,
        int $creditApplied,
        int $cashRefund,
        ?FinancialAccount $refundAccount,
    ) {
        $lines = [
            new JournalLineData(
                FinancialAccount::query()->where('code', '4135-VENTAS')->sole()->id,
                $totalRefund,
                0,
                $sale->customer_person_id,
                'Reversión de ingreso por devolución',
            ),
        ];
        if ($creditApplied > 0) {
            $lines[] = new JournalLineData(
                FinancialAccount::query()->where('code', '1305-CXC')->sole()->id,
                0,
                $creditApplied,
                $sale->customer_person_id,
                'Crédito aplicado a cartera',
            );
        }
        if ($cashRefund > 0 && $refundAccount) {
            $lines[] = new JournalLineData(
                $refundAccount->id,
                0,
                $cashRefund,
                $sale->customer_person_id,
                'Reembolso al cliente',
            );
        }
        if ($totalCost > 0) {
            $lines[] = new JournalLineData(
                FinancialAccount::query()->where('code', '1435-INVENTARIO')->sole()->id,
                $totalCost,
                0,
                $sale->customer_person_id,
                'Reintegro de inventario',
            );
            $lines[] = new JournalLineData(
                FinancialAccount::query()->where('code', '6135-COSTO-VENTAS')->sole()->id,
                0,
                $totalCost,
                $sale->customer_person_id,
                'Reversión del costo de venta',
            );
        }

        return $this->postJournalEntry->execute(new JournalEntryData(
            $returnedAt,
            "Devolución {$saleReturn->document_number}: {$saleReturn->reason}",
            $actor,
            $lines,
            $saleReturn,
        ));
    }
}
