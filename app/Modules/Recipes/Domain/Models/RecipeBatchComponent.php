<?php

namespace App\Modules\Recipes\Domain\Models;

use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Recipes\Domain\Enums\RecipeBatchComponentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['recipe_version_id', 'type', 'label', 'item_id', 'quantity_per_batch', 'unit_id', 'amount_per_batch', 'sort_order'])]
class RecipeBatchComponent extends Model
{
    use HasUlids;

    public function version(): BelongsTo { return $this->belongsTo(RecipeVersion::class, 'recipe_version_id'); }
    public function item(): BelongsTo { return $this->belongsTo(Item::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }

    protected function casts(): array
    {
        return ['type' => RecipeBatchComponentType::class, 'quantity_per_batch' => 'decimal:6', 'amount_per_batch' => 'integer', 'sort_order' => 'integer'];
    }
}
