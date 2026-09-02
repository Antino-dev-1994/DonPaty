<?php

namespace App\Modules\Household\Domain\Models;

use App\Models\User;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use App\Modules\Finance\Domain\Models\JournalEntry;
use App\Modules\Household\Domain\Enums\HouseholdTransactionType;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['document_number', 'transaction_type', 'person_id', 'financial_category_id', 'from_account_id', 'to_account_id', 'occurred_at', 'amount', 'description', 'counts_for_budget', 'journal_entry_id', 'created_by'])]
class HouseholdTransaction extends Model
{
    use HasUlids;

    public function person(): BelongsTo { return $this->belongsTo(Person::class); }
    public function category(): BelongsTo { return $this->belongsTo(FinancialCategory::class, 'financial_category_id'); }
    public function fromAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class, 'from_account_id'); }
    public function toAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class, 'to_account_id'); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    protected function casts(): array
    {
        return ['transaction_type' => HouseholdTransactionType::class, 'occurred_at' => 'datetime', 'amount' => 'integer', 'counts_for_budget' => 'boolean'];
    }
}
