<?php

namespace App\Modules\Inventory\Domain\Models;

use App\Modules\Catalog\Domain\Models\ProductPresentation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['inventory_movement_id', 'presentation_id', 'quantity_in', 'quantity_out', 'unit_cost', 'total_cost', 'balance_before', 'balance_after'])]
class InventoryMovementLine extends Model
{
    use HasUlids;

    public function movement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class, 'inventory_movement_id');
    }

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(ProductPresentation::class);
    }

    protected function casts(): array
    {
        return [
            'quantity_in' => 'decimal:6', 'quantity_out' => 'decimal:6',
            'balance_before' => 'decimal:6', 'balance_after' => 'decimal:6',
            'unit_cost' => 'integer', 'total_cost' => 'integer',
        ];
    }
}
