<?php

namespace App\Modules\Finance\Domain\Models;

use App\Modules\People\Domain\Models\Person;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['journal_entry_id', 'financial_account_id', 'debit_amount', 'credit_amount', 'person_id', 'description'])]
class JournalLine extends Model
{
    use HasUlids;
    public function entry(): BelongsTo { return $this->belongsTo(JournalEntry::class, 'journal_entry_id'); }
    public function account(): BelongsTo { return $this->belongsTo(FinancialAccount::class, 'financial_account_id'); }
    public function person(): BelongsTo { return $this->belongsTo(Person::class); }
    protected function casts(): array { return ['debit_amount' => 'integer', 'credit_amount' => 'integer']; }
}
