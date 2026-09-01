<?php

namespace App\Modules\Pricing\Domain\Models;

use App\Modules\Catalog\Domain\Models\ProductPresentation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['price_list_id', 'presentation_id', 'price', 'minimum_price'])]
class PriceListItem extends Model
{
    use HasUlids;

    public function priceList(): BelongsTo { return $this->belongsTo(PriceList::class); }
    public function presentation(): BelongsTo { return $this->belongsTo(ProductPresentation::class); }
    protected function casts(): array { return ['price' => 'integer', 'minimum_price' => 'integer']; }
}
