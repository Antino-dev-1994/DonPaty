<?php

namespace App\Modules\Pricing\Domain\Models;

use App\Modules\Pricing\Domain\Enums\PriceListType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'starts_at', 'ends_at', 'is_default', 'is_active'])]
class PriceList extends Model
{
    use HasUlids;

    public function items(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function isApplicableAt(\DateTimeInterface $date): bool
    {
        return $this->is_active
            && ($this->starts_at === null || $this->starts_at->lte($date))
            && ($this->ends_at === null || $this->ends_at->gte($date));
    }

    protected function casts(): array
    {
        return ['type' => PriceListType::class, 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_default' => 'boolean', 'is_active' => 'boolean'];
    }
}
