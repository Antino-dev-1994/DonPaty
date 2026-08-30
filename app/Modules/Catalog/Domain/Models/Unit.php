<?php

namespace App\Modules\Catalog\Domain\Models;

use App\Modules\Catalog\Domain\Enums\UnitDimension;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'dimension', 'scale_to_base', 'precision', 'is_active'])]
class Unit extends Model
{
    use HasUlids;

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'base_unit_id');
    }

    protected function casts(): array
    {
        return [
            'dimension' => UnitDimension::class,
            'scale_to_base' => 'decimal:8',
            'precision' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
