<?php

namespace App\Modules\Purchasing\Domain\Models;

use App\Modules\Catalog\Domain\Models\ProductPresentation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['purchase_id', 'presentation_id', 'ordered_quantity', 'received_quantity', 'returned_quantity', 'unit_price', 'allocated_additional_cost', 'line_total'])]
class PurchaseLine extends Model
{
    use HasUlids;

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(ProductPresentation::class);
    }

    protected function casts(): array
    {
        return [
            'ordered_quantity' => 'decimal:6', 'received_quantity' => 'decimal:6', 'returned_quantity' => 'decimal:6',
            'unit_price' => 'integer', 'allocated_additional_cost' => 'integer', 'line_total' => 'integer',
        ];
    }
}
