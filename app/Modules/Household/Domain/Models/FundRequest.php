<?php

namespace App\Modules\Household\Domain\Models;

use App\Models\User;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Models\ExpenseRecord;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Household\Domain\Enums\FundRequestStatus;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['document_number', 'requester_person_id', 'source_scope', 'amount', 'reason', 'needed_at', 'status', 'created_by', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'decision_notes', 'paid_by', 'paid_at', 'source_account_id', 'destination_account_id', 'business_expense_record_id', 'household_transaction_id', 'confirmed_by', 'confirmed_at'])]
class FundRequest extends Model
{
    use HasUlids;

    public function requester(): BelongsTo { return $this->belongsTo(Person::class, 'requester_person_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function payer(): BelongsTo { return $this->belongsTo(User::class, 'paid_by'); }
    public function sourceAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class, 'source_account_id'); }
    public function destinationAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class, 'destination_account_id'); }
    public function businessExpense(): BelongsTo { return $this->belongsTo(ExpenseRecord::class, 'business_expense_record_id'); }
    public function householdTransaction(): BelongsTo { return $this->belongsTo(HouseholdTransaction::class); }
    public function histories(): HasMany { return $this->hasMany(FundRequestHistory::class); }

    protected function casts(): array
    {
        return ['source_scope' => FinancialScope::class, 'amount' => 'integer', 'needed_at' => 'date', 'status' => FundRequestStatus::class, 'approved_at' => 'datetime', 'rejected_at' => 'datetime', 'paid_at' => 'datetime', 'confirmed_at' => 'datetime'];
    }
}
