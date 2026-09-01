<?php

namespace App\Modules\Recipes\Domain\Models;

use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\Unit;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['recipe_compatible_product_id', 'item_id', 'quantity_per_unit', 'unit_id'])]
class ProductFinishingComponent extends Model
{
    use HasUlids;
    public function compatibleProduct(): BelongsTo { return $this->belongsTo(RecipeCompatibleProduct::class, 'recipe_compatible_product_id'); }
    public function item(): BelongsTo { return $this->belongsTo(Item::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
    protected function casts(): array { return ['quantity_per_unit' => 'decimal:6']; }
}
