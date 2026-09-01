<?php

namespace App\Modules\Finance\Domain\Models;

use App\Modules\Finance\Domain\Enums\FinancialAccountType;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'account_type', 'scope', 'person_id', 'parent_id', 'accepts_payments', 'is_active'])]
class FinancialAccount extends Model
{
    use HasUlids;

    public function person(): BelongsTo { return $this->belongsTo(Person::class); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function journalLines(): HasMany { return $this->hasMany(JournalLine::class); }

    protected function casts(): array
    {
        return ['account_type' => FinancialAccountType::class, 'scope' => FinancialScope::class, 'accepts_payments' => 'boolean', 'is_active' => 'boolean'];
    }
}
