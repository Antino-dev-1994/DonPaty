<?php

namespace App\Modules\Purchasing\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['purchase_return_id', 'purchase_line_id', 'quantity', 'unit_cost', 'total_cost'])]
class PurchaseReturnLine extends Model
{
    use HasUlids;

    public function purchaseReturn(): BelongsTo { return $this->belongsTo(PurchaseReturn::class); }
    public function purchaseLine(): BelongsTo { return $this->belongsTo(PurchaseLine::class); }

    protected function casts(): array
    {
        return ['quantity' => 'decimal:6', 'unit_cost' => 'integer', 'total_cost' => 'integer'];
    }
}
