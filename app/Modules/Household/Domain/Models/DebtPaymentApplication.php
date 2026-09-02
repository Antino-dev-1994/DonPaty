<?php

namespace App\Modules\Household\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['debt_payment_id', 'debt_installment_id', 'principal_amount', 'interest_amount'])]
class DebtPaymentApplication extends Model
{
    use HasUlids;
    public function debtPayment(): BelongsTo { return $this->belongsTo(DebtPayment::class); }
    public function installment(): BelongsTo { return $this->belongsTo(DebtInstallment::class, 'debt_installment_id'); }
    protected function casts(): array { return ['principal_amount' => 'integer', 'interest_amount' => 'integer']; }
}
