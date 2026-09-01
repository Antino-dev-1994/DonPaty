<?php

namespace App\Modules\Finance\Domain\Models;

use App\Models\User;
use App\Modules\Finance\Domain\Enums\FinancialDocumentStatus;
use App\Modules\Finance\Domain\Enums\PaymentDirection;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['document_number', 'direction', 'person_id', 'paid_at', 'amount', 'financial_account_id', 'status', 'reference', 'created_by'])]
class Payment extends Model
{
    use HasUlids;

    public function person(): BelongsTo { return $this->belongsTo(Person::class); }
    public function financialAccount(): BelongsTo { return $this->belongsTo(FinancialAccount::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function allocations(): HasMany { return $this->hasMany(PaymentAllocation::class); }

    protected function casts(): array
    {
        return ['direction' => PaymentDirection::class, 'paid_at' => 'datetime', 'amount' => 'integer', 'status' => FinancialDocumentStatus::class];
    }
}
