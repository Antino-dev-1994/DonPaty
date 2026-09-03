<?php

namespace App\Modules\Dashboard\Application;

use App\Modules\CostAccounting\Domain\Enums\CostPeriodStatus;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\Dashboard\Application\Data\ReportDateRange;
use App\Modules\Finance\Domain\Enums\FinancialAccountType;
use App\Modules\Finance\Domain\Enums\FinancialDocumentStatus;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Enums\PaymentDirection;
use App\Modules\Finance\Domain\Models\JournalEntry;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\Sales\Domain\Models\Sale;
use App\Modules\Sales\Domain\Models\SaleReturn;
use Illuminate\Support\Collection;

class BusinessPerformanceQuery
{
    /** @return array<string, mixed> */
    public function execute(ReportDateRange $range): array
    {
        $payments = Payment::query()
            ->with('financialAccount:id,name,scope')
            ->where('status', FinancialDocumentStatus::Confirmed)
            ->whereBetween('paid_at', [$range->from, $range->to])
            ->whereHas('financialAccount', fn ($query) => $query->where('scope', FinancialScope::Business))
            ->orderBy('paid_at')->get();
        $entries = JournalEntry::query()
            ->with(['lines' => fn ($query) => $query->whereHas('account', fn ($account) => $account->where('scope', FinancialScope::Business)->whereIn('account_type', [FinancialAccountType::Revenue, FinancialAccountType::Expense]))->with('account:id,code,name,account_type,scope')])
            ->where('status', FinancialDocumentStatus::Confirmed)
            ->whereBetween('effective_at', [$range->from, $range->to])
            ->whereHas('lines.account', fn ($query) => $query->where('scope', FinancialScope::Business)->whereIn('account_type', [FinancialAccountType::Revenue, FinancialAccountType::Expense]))
            ->orderBy('effective_at')->get();
        $cashIn = (int) $payments->where('direction', PaymentDirection::Incoming)->sum('amount');
        $cashOut = (int) $payments->where('direction', PaymentDirection::Outgoing)->sum('amount');
        [$revenue, $expenses] = $this->resultTotals($entries);
        $sales = Sale::query()->whereIn('status', ['confirmed', 'partially_paid', 'paid'])->whereBetween('sold_at', [$range->from, $range->to]);
        $returns = SaleReturn::query()->where('status', FinancialDocumentStatus::Confirmed)->whereBetween('returned_at', [$range->from, $range->to]);
        $salesTotal = (int) (clone $sales)->sum('total');
        $salesCost = (int) (clone $sales)->sum('cost_of_goods_sold');
        $refund = (int) (clone $returns)->sum('total_refund');
        $returnedCost = (int) (clone $returns)->sum('total_cost');

        return [
            'totals' => [
                'cash_in' => $cashIn,
                'cash_out' => $cashOut,
                'cash_flow' => $cashIn - $cashOut,
                'revenue' => $revenue,
                'expenses' => $expenses,
                'profit' => $revenue - $expenses,
                'sales' => $salesTotal - $refund,
                'sales_cost' => $salesCost - $returnedCost,
                'sales_margin' => ($salesTotal - $refund) - ($salesCost - $returnedCost),
            ],
            'daily' => $this->daily($range, $payments, $entries),
            'documents' => $this->documents($payments, $entries),
            'provisional' => $this->isProvisional($range),
        ];
    }

    /** @param Collection<int, JournalEntry> $entries
     *  @return array{int, int}
     */
    private function resultTotals(Collection $entries): array
    {
        $revenue = 0;
        $expenses = 0;
        foreach ($entries->flatMap->lines as $line) {
            if ($line->account->account_type === FinancialAccountType::Revenue) {
                $revenue += $line->credit_amount - $line->debit_amount;
            } else {
                $expenses += $line->debit_amount - $line->credit_amount;
            }
        }

        return [$revenue, $expenses];
    }

    /** @param Collection<int, Payment> $payments
     *  @param Collection<int, JournalEntry> $entries
     *  @return list<array<string, int|string>>
     */
    private function daily(ReportDateRange $range, Collection $payments, Collection $entries): array
    {
        $rows = [];
        for ($date = $range->from->startOfDay(); $date->lte($range->to); $date = $date->addDay()) {
            $rows[$date->format('Y-m-d')] = ['date' => $date->format('Y-m-d'), 'cash_in' => 0, 'cash_out' => 0, 'revenue' => 0, 'expenses' => 0, 'cash_flow' => 0, 'profit' => 0];
        }
        foreach ($payments as $payment) {
            $key = $payment->paid_at->format('Y-m-d');
            $rows[$key][$payment->direction === PaymentDirection::Incoming ? 'cash_in' : 'cash_out'] += $payment->amount;
        }
        foreach ($entries as $entry) {
            $key = $entry->effective_at->format('Y-m-d');
            foreach ($entry->lines as $line) {
                if ($line->account->account_type === FinancialAccountType::Revenue) {
                    $rows[$key]['revenue'] += $line->credit_amount - $line->debit_amount;
                } else {
                    $rows[$key]['expenses'] += $line->debit_amount - $line->credit_amount;
                }
            }
        }
        foreach ($rows as &$row) {
            $row['cash_flow'] = $row['cash_in'] - $row['cash_out'];
            $row['profit'] = $row['revenue'] - $row['expenses'];
        }

        return array_values($rows);
    }

    /** @param Collection<int, Payment> $payments
     *  @param Collection<int, JournalEntry> $entries
     *  @return list<array<string, int|string>>
     */
    private function documents(Collection $payments, Collection $entries): array
    {
        $cash = $payments->map(fn (Payment $payment) => [
            'id' => $payment->id,
            'kind' => $payment->direction === PaymentDirection::Incoming ? 'Entrada de dinero' : 'Salida de dinero',
            'document' => $payment->document_number,
            'date' => $payment->paid_at->format('Y-m-d H:i'),
            'description' => $payment->reference ?: $payment->financialAccount->name,
            'amount' => $payment->direction === PaymentDirection::Incoming ? $payment->amount : -$payment->amount,
        ]);
        $result = $entries->map(function (JournalEntry $entry): array {
            [$revenue, $expenses] = $this->resultTotals(collect([$entry]));

            return ['id' => $entry->id, 'kind' => 'Resultado económico', 'document' => $entry->document_number, 'date' => $entry->effective_at->format('Y-m-d H:i'), 'description' => $entry->description, 'amount' => $revenue - $expenses];
        });

        return $cash->concat($result)->sortByDesc('date')->take(100)->values()->all();
    }

    private function isProvisional(ReportDateRange $range): bool
    {
        for ($month = $range->from->startOfMonth(); $month->lte($range->to); $month = $month->addMonth()) {
            if (! CostPeriod::query()->where('year', $month->year)->where('month', $month->month)->where('status', CostPeriodStatus::Closed)->exists()) {
                return true;
            }
        }

        return false;
    }
}
