<?php

namespace App\Modules\Purchasing\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['purchase_receipt_id', 'purchase_line_id', 'quantity', 'unit_cost', 'total_cost'])]
class PurchaseReceiptLine extends Model
{
    use HasUlids;

    public function receipt(): BelongsTo { return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id'); }
    public function purchaseLine(): BelongsTo { return $this->belongsTo(PurchaseLine::class); }

    protected function casts(): array
    {
        return ['quantity' => 'decimal:6', 'unit_cost' => 'integer', 'total_cost' => 'integer'];
    }
}
