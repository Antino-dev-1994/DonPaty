<?php

namespace App\Modules\Finance\Domain\Models;

use App\Models\User;
use App\Modules\Finance\Domain\Enums\FinancialDocumentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['document_number', 'effective_at', 'description', 'status', 'source_type', 'source_id', 'posted_by', 'reversal_of_id'])]
class JournalEntry extends Model
{
    use HasUlids;
    public function source(): MorphTo { return $this->morphTo(); }
    public function poster(): BelongsTo { return $this->belongsTo(User::class, 'posted_by'); }
    public function lines(): HasMany { return $this->hasMany(JournalLine::class); }
    protected function casts(): array { return ['effective_at' => 'datetime', 'status' => FinancialDocumentStatus::class]; }
}
