<?php

namespace App\Modules\Household\Domain\Models;

use App\Models\User;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Household\Domain\Enums\SavingsGoalStatus;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'person_id', 'financial_account_id', 'target_amount', 'target_date', 'status', 'created_by'])]
class SavingsGoal extends Model
{
    use HasUlids;
    public function person(): BelongsTo { return $this->belongsTo(Person::class); }
    public function financialAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class); }
    public function contributions(): HasMany { return $this->hasMany(SavingsContribution::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    protected function casts(): array { return ['target_amount' => 'integer', 'target_date' => 'date', 'status' => SavingsGoalStatus::class]; }
}
