<?php

namespace App\Modules\Purchasing\Domain\Models;

use App\Models\User;
use App\Modules\People\Domain\Models\Person;
use App\Modules\Purchasing\Domain\Enums\PurchaseDocumentStatus;
use App\Modules\Purchasing\Domain\Enums\PurchasePaymentCondition;
use App\Modules\Purchasing\Domain\Enums\PurchasePaymentStatus;
use App\Modules\Purchasing\Domain\Enums\PurchaseReceiptStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['document_number', 'supplier_person_id', 'supplier_document_number', 'issued_at', 'due_at', 'payment_condition', 'status', 'receipt_status', 'payment_status', 'subtotal', 'additional_costs', 'total', 'paid_amount', 'balance_amount', 'notes', 'created_by', 'reversal_of_id'])]
class Purchase extends Model
{
    use HasUlids;

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'supplier_person_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseLine::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    protected function casts(): array
    {
        return [
            'issued_at' => 'date', 'due_at' => 'date',
            'payment_condition' => PurchasePaymentCondition::class,
            'status' => PurchaseDocumentStatus::class,
            'receipt_status' => PurchaseReceiptStatus::class,
            'payment_status' => PurchasePaymentStatus::class,
            'subtotal' => 'integer', 'additional_costs' => 'integer', 'total' => 'integer',
            'paid_amount' => 'integer', 'balance_amount' => 'integer',
        ];
    }
}
