<?php

namespace App\Modules\Catalog\Domain\Models;

use App\Modules\Catalog\Domain\Enums\ItemType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'name', 'type', 'base_unit_id', 'minimum_stock', 'allow_negative_stock', 'is_active', 'notes'])]
class Item extends Model
{
    use HasUlids, SoftDeletes;

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function presentations(): HasMany
    {
        return $this->hasMany(ProductPresentation::class);
    }

    protected function casts(): array
    {
        return [
            'type' => ItemType::class,
            'minimum_stock' => 'decimal:6',
            'allow_negative_stock' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
