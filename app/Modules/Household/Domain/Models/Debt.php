<?php

namespace App\Modules\Household\Domain\Models;

use App\Models\User;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\JournalEntry;
use App\Modules\Household\Domain\Enums\DebtDirection;
use App\Modules\Household\Domain\Enums\DebtStatus;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['document_number', 'person_id', 'direction', 'description', 'principal_amount', 'annual_interest_rate', 'start_date', 'financial_account_id', 'status', 'principal_paid', 'interest_paid', 'opening_journal_entry_id', 'created_by'])]
class Debt extends Model
{
    use HasUlids;
    public function person(): BelongsTo { return $this->belongsTo(Person::class); }
    public function financialAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class); }
    public function openingJournalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class, 'opening_journal_entry_id'); }
    public function installments(): HasMany { return $this->hasMany(DebtInstallment::class); }
    public function payments(): HasMany { return $this->hasMany(DebtPayment::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    protected function casts(): array { return ['direction' => DebtDirection::class, 'principal_amount' => 'integer', 'annual_interest_rate' => 'decimal:6', 'start_date' => 'date', 'status' => DebtStatus::class, 'principal_paid' => 'integer', 'interest_paid' => 'integer']; }
}
