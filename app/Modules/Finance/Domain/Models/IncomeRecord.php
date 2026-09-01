<?php

namespace App\Modules\Finance\Domain\Models;

use App\Models\User;
use App\Modules\Finance\Domain\Enums\BusinessRecordStatus;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[Fillable(['document_number', 'financial_category_id', 'person_id', 'effective_at', 'due_at', 'description', 'total_amount', 'paid_amount', 'balance_amount', 'status', 'journal_entry_id', 'created_by'])]
class IncomeRecord extends Model
{
    use HasUlids;

    public function category(): BelongsTo { return $this->belongsTo(FinancialCategory::class, 'financial_category_id'); }
    public function person(): BelongsTo { return $this->belongsTo(Person::class); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function receivable(): MorphOne { return $this->morphOne(\App\Modules\Sales\Domain\Models\Receivable::class, 'source'); }

    protected function casts(): array
    {
        return ['effective_at' => 'datetime', 'due_at' => 'date', 'total_amount' => 'integer', 'paid_amount' => 'integer', 'balance_amount' => 'integer', 'status' => BusinessRecordStatus::class];
    }
}
