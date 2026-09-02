<?php

namespace App\Modules\Household\Domain\Models;

use App\Models\User;
use App\Modules\Household\Domain\Enums\HouseholdBudgetStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['year', 'month', 'status', 'created_by', 'confirmed_at', 'confirmed_by'])]
class HouseholdBudget extends Model
{
    use HasUlids;
    public function lines(): HasMany { return $this->hasMany(HouseholdBudgetLine::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function confirmer(): BelongsTo { return $this->belongsTo(User::class, 'confirmed_by'); }
    public function label(): string { return sprintf('%04d-%02d', $this->year, $this->month); }
    protected function casts(): array { return ['year' => 'integer', 'month' => 'integer', 'status' => HouseholdBudgetStatus::class, 'confirmed_at' => 'datetime']; }
}
