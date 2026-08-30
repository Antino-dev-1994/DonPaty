<?php

namespace App\Modules\Inventory\Domain\Models;

use App\Modules\Catalog\Domain\Models\ProductPresentation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['presentation_id', 'physical_quantity', 'reserved_quantity', 'average_unit_cost', 'updated_at'])]
class InventoryBalance extends Model
{
    use HasUlids;

    public const CREATED_AT = null;

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(ProductPresentation::class);
    }

    public function availableQuantity(): string
    {
        return bcsub($this->physical_quantity, $this->reserved_quantity, 6);
    }

    protected function casts(): array
    {
        return [
            'physical_quantity' => 'decimal:6', 'reserved_quantity' => 'decimal:6',
            'average_unit_cost' => 'integer', 'updated_at' => 'datetime',
        ];
    }
}
