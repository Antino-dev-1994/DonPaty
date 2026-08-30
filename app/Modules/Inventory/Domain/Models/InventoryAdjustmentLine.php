<?php

namespace App\Modules\Inventory\Domain\Models;

use App\Modules\Catalog\Domain\Models\ProductPresentation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['inventory_adjustment_id', 'presentation_id', 'expected_quantity', 'counted_quantity', 'difference_quantity', 'unit_cost'])]
class InventoryAdjustmentLine extends Model
{
    use HasUlids;

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(InventoryAdjustment::class, 'inventory_adjustment_id');
    }

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(ProductPresentation::class);
    }

    protected function casts(): array
    {
        return [
            'expected_quantity' => 'decimal:6', 'counted_quantity' => 'decimal:6',
            'difference_quantity' => 'decimal:6', 'unit_cost' => 'integer',
        ];
    }
}
