<?php

namespace App\Modules\Recipes\Domain\Models;

use App\Models\User;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Recipes\Domain\Enums\RecipeVersionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['recipe_id', 'version_number', 'status', 'effective_from', 'effective_to', 'reference_flour_quantity', 'reference_flour_unit_id', 'expected_dough_yield', 'yield_unit_id', 'expected_waste_percentage', 'instructions', 'created_by', 'activated_by', 'activated_at'])]
class RecipeVersion extends Model
{
    use HasUlids;
    public function recipe(): BelongsTo { return $this->belongsTo(Recipe::class); }
    public function referenceFlourUnit(): BelongsTo { return $this->belongsTo(Unit::class, 'reference_flour_unit_id'); }
    public function yieldUnit(): BelongsTo { return $this->belongsTo(Unit::class, 'yield_unit_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function activator(): BelongsTo { return $this->belongsTo(User::class, 'activated_by'); }
    public function ingredients(): HasMany { return $this->hasMany(RecipeIngredient::class)->orderBy('sort_order'); }
    public function compatibleProducts(): HasMany { return $this->hasMany(RecipeCompatibleProduct::class); }

    public function scopeApplicableOn(Builder $query, string $date): Builder
    {
        return $query->where('status', RecipeVersionStatus::Published)->whereDate('effective_from', '<=', $date)
            ->where(fn (Builder $query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date));
    }

    public function isDraft(): bool { return $this->status === RecipeVersionStatus::Draft; }

    protected function casts(): array
    {
        return ['version_number' => 'integer', 'status' => RecipeVersionStatus::class, 'effective_from' => 'date', 'effective_to' => 'date', 'reference_flour_quantity' => 'decimal:6', 'expected_dough_yield' => 'decimal:6', 'expected_waste_percentage' => 'decimal:4', 'activated_at' => 'datetime'];
    }
}
