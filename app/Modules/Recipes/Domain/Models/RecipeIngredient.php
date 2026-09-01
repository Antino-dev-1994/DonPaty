<?php

namespace App\Modules\Recipes\Domain\Models;

use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Recipes\Domain\Enums\IngredientRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['recipe_version_id', 'item_id', 'presentation_id', 'ingredient_role', 'quantity', 'unit_id', 'baker_percentage', 'allows_substitution', 'sort_order'])]
class RecipeIngredient extends Model
{
    use HasUlids;
    public function version(): BelongsTo { return $this->belongsTo(RecipeVersion::class, 'recipe_version_id'); }
    public function item(): BelongsTo { return $this->belongsTo(Item::class); }
    public function presentation(): BelongsTo { return $this->belongsTo(ProductPresentation::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
    protected function casts(): array { return ['ingredient_role' => IngredientRole::class, 'quantity' => 'decimal:6', 'baker_percentage' => 'decimal:4', 'allows_substitution' => 'boolean', 'sort_order' => 'integer']; }
}
