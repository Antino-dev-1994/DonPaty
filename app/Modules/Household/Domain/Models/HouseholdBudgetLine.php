<?php

namespace App\Modules\Household\Domain\Models;

use App\Modules\Finance\Domain\Models\FinancialCategory;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['household_budget_id', 'financial_category_id', 'person_id', 'budgeted_amount'])]
class HouseholdBudgetLine extends Model
{
    use HasUlids;
    public function budget(): BelongsTo { return $this->belongsTo(HouseholdBudget::class, 'household_budget_id'); }
    public function category(): BelongsTo { return $this->belongsTo(FinancialCategory::class, 'financial_category_id'); }
    public function person(): BelongsTo { return $this->belongsTo(Person::class); }
    protected function casts(): array { return ['budgeted_amount' => 'integer']; }
}
