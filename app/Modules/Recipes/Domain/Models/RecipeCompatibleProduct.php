<?php

namespace App\Modules\Recipes\Domain\Models;

use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['recipe_version_id', 'presentation_id', 'dough_weight_per_unit', 'dough_weight_unit_id', 'baking_loss_percentage', 'cost_weight_factor'])]
class RecipeCompatibleProduct extends Model
{
    use HasUlids;
    public function version(): BelongsTo { return $this->belongsTo(RecipeVersion::class, 'recipe_version_id'); }
    public function presentation(): BelongsTo { return $this->belongsTo(ProductPresentation::class); }
    public function doughWeightUnit(): BelongsTo { return $this->belongsTo(Unit::class, 'dough_weight_unit_id'); }
    public function finishingComponents(): HasMany { return $this->hasMany(ProductFinishingComponent::class); }
    protected function casts(): array { return ['dough_weight_per_unit' => 'decimal:6', 'baking_loss_percentage' => 'decimal:4', 'cost_weight_factor' => 'decimal:6']; }
}
