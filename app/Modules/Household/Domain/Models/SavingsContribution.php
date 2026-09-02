<?php

namespace App\Modules\Household\Domain\Models;

use App\Models\User;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\JournalEntry;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['savings_goal_id', 'source_account_id', 'amount', 'contributed_at', 'journal_entry_id', 'created_by'])]
class SavingsContribution extends Model
{
    use HasUlids;
    public function goal(): BelongsTo { return $this->belongsTo(SavingsGoal::class, 'savings_goal_id'); }
    public function sourceAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class, 'source_account_id'); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    protected function casts(): array { return ['amount' => 'integer', 'contributed_at' => 'datetime']; }
}
