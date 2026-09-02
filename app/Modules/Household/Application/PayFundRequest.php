<?php

namespace App\Modules\Household\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\CostAccounting\Domain\Enums\CostPeriodStatus;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\Finance\Application\Data\RegisterExpenseData;
use App\Modules\Finance\Application\RegisterExpense;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use App\Modules\Household\Application\Data\HouseholdTransactionData;
use App\Modules\Household\Application\Data\PayFundRequestData;
use App\Modules\Household\Domain\Enums\FundRequestStatus;
use App\Modules\Household\Domain\Enums\HouseholdTransactionType;
use App\Modules\Household\Domain\Models\FundRequest;
use DomainException;
use Illuminate\Support\Facades\DB;

class PayFundRequest
{
    public function __construct(
        private readonly RecordHouseholdTransaction $householdTransactions,
        private readonly RegisterExpense $expenses,
        private readonly RecordFundRequestHistory $history,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(FundRequest $request, PayFundRequestData $data): FundRequest
    {
        return DB::transaction(function () use ($request, $data): FundRequest {
            $request = FundRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($request->status !== FundRequestStatus::Approved) {
                throw new DomainException('Solo se puede pagar una solicitud aprobada.');
            }
            $source = FinancialAccount::query()->where('is_active', true)->where('accepts_payments', true)->findOrFail($data->sourceAccountId);
            $destination = FinancialAccount::query()->where('scope', FinancialScope::Personal)->where('person_id', $request->requester_person_id)->where('is_active', true)->findOrFail($data->destinationAccountId);
            if ($source->scope !== $request->source_scope) {
                throw new DomainException('La cuenta de origen no corresponde al fondo aprobado.');
            }

            $expenseId = null;
            if ($request->source_scope === FinancialScope::Business) {
                $period = CostPeriod::query()->where('year', $data->paidAt->year)->where('month', $data->paidAt->month)->where('status', CostPeriodStatus::Open)->first();
                if (! $period) {
                    throw new DomainException('El pago desde el negocio requiere un periodo de costos abierto para ese mes.');
                }
                $category = FinancialCategory::query()->where('code', 'GAS-MANO-OBRA')->sole();
                $expense = $this->expenses->execute(new RegisterExpenseData(
                    $category->id,
                    $request->requester_person_id,
                    $period->id,
                    null,
                    $data->paidAt,
                    null,
                    "Solicitud {$request->document_number}: {$request->reason}",
                    $request->amount,
                    $request->amount,
                    $source->id,
                    $request->document_number,
                    $data->payer,
                ));
                $expenseId = $expense->id;
                $categoryId = FinancialCategory::query()->where('code', 'HOG-ING-MANO-OBRA')->sole()->id;
                $transaction = $this->householdTransactions->execute(new HouseholdTransactionData(
                    HouseholdTransactionType::Income,
                    $request->requester_person_id,
                    $categoryId,
                    null,
                    $destination->id,
                    $data->paidAt,
                    $request->amount,
                    "Pago de mano de obra {$request->document_number}",
                    false,
                    $data->payer,
                ));
            } else {
                $transaction = $this->householdTransactions->execute(new HouseholdTransactionData(
                    HouseholdTransactionType::Transfer,
                    $request->requester_person_id,
                    null,
                    $source->id,
                    $destination->id,
                    $data->paidAt,
                    $request->amount,
                    "Pago de solicitud {$request->document_number}",
                    false,
                    $data->payer,
                ));
            }

            $before = $request->toArray();
            $request->update([
                'status' => FundRequestStatus::Paid,
                'paid_by' => $data->payer->id,
                'paid_at' => $data->paidAt,
                'source_account_id' => $source->id,
                'destination_account_id' => $destination->id,
                'business_expense_record_id' => $expenseId,
                'household_transaction_id' => $transaction->id,
            ]);
            $this->history->execute($request, FundRequestStatus::Approved, FundRequestStatus::Paid, $data->payer);
            $this->audit->execute('household.fund_request_paid', $request, $data->payer, $before, $request->fresh()->toArray());

            return $request->fresh(['businessExpense', 'householdTransaction']);
        });
    }
}
