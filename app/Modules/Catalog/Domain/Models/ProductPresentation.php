<?php

namespace App\Modules\Catalog\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['item_id', 'sku', 'name', 'stock_unit_id', 'conversion_to_item_base', 'is_purchasable', 'is_sellable', 'is_stockable', 'is_active', 'barcode', 'minimum_sale_price'])]
class ProductPresentation extends Model
{
    use HasUlids;

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function stockUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'stock_unit_id');
    }

    public function packageComponents(): HasMany
    {
        return $this->hasMany(PackageComponent::class, 'package_presentation_id');
    }

    public function isPackage(): bool
    {
        return $this->packageComponents()->exists();
    }

    protected function casts(): array
    {
        return [
            'conversion_to_item_base' => 'decimal:8',
            'is_purchasable' => 'boolean',
            'is_sellable' => 'boolean',
            'is_stockable' => 'boolean',
            'is_active' => 'boolean',
            'minimum_sale_price' => 'integer',
        ];
    }
}
