<?php

namespace App\Modules\Finance\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['opened_on', 'cash_amount', 'nequi_amount', 'notes', 'opened_by', 'journal_entry_id'])]
class BusinessOpening extends Model
{
    use HasUlids;

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    protected function casts(): array
    {
        return ['opened_on' => 'date', 'cash_amount' => 'integer', 'nequi_amount' => 'integer'];
    }
}
