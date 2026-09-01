<?php

namespace App\Modules\Finance\Domain\Models;

use App\Modules\CostAccounting\Domain\Enums\CostType;
use App\Modules\Finance\Domain\Enums\FinancialCategoryType;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'record_type', 'scope', 'ledger_account_id', 'parent_id', 'cost_type', 'is_active'])]
class FinancialCategory extends Model
{
    use HasUlids;

    public function ledgerAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class, 'ledger_account_id'); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id'); }

    protected function casts(): array
    {
        return [
            'record_type' => FinancialCategoryType::class,
            'scope' => FinancialScope::class,
            'cost_type' => CostType::class,
            'is_active' => 'boolean',
        ];
    }
}
