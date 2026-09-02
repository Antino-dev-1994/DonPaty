<?php

namespace App\Modules\Household\Domain\Models;

use App\Models\User;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\JournalEntry;
use App\Modules\Finance\Domain\Models\Payment;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['document_number', 'debt_id', 'paid_at', 'amount', 'principal_amount', 'interest_amount', 'financial_account_id', 'payment_id', 'journal_entry_id', 'created_by'])]
class DebtPayment extends Model
{
    use HasUlids;
    public function debt(): BelongsTo { return $this->belongsTo(Debt::class); }
    public function financialAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class); }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }
    public function applications(): HasMany { return $this->hasMany(DebtPaymentApplication::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    protected function casts(): array { return ['paid_at' => 'datetime', 'amount' => 'integer', 'principal_amount' => 'integer', 'interest_amount' => 'integer']; }
}
