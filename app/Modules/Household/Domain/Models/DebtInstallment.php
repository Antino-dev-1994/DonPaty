<?php

namespace App\Modules\Household\Domain\Models;

use App\Modules\Household\Domain\Enums\InstallmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['debt_id', 'sequence', 'due_at', 'principal_amount', 'interest_amount', 'principal_paid', 'interest_paid', 'status'])]
class DebtInstallment extends Model
{
    use HasUlids;
    public function debt(): BelongsTo { return $this->belongsTo(Debt::class); }
    public function applications(): HasMany { return $this->hasMany(DebtPaymentApplication::class); }
    protected function casts(): array { return ['sequence' => 'integer', 'due_at' => 'date', 'principal_amount' => 'integer', 'interest_amount' => 'integer', 'principal_paid' => 'integer', 'interest_paid' => 'integer', 'status' => InstallmentStatus::class]; }
}
