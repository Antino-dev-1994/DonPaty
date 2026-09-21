<?php

namespace App\Modules\Production\Domain\Models;

use App\Modules\Recipes\Domain\Models\RecipeBatchComponent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['production_order_id', 'recipe_batch_component_id', 'label', 'amount'])]
class ProductionBatchCost extends Model
{
    use HasUlids;

    public function order(): BelongsTo { return $this->belongsTo(ProductionOrder::class, 'production_order_id'); }
    public function recipeBatchComponent(): BelongsTo { return $this->belongsTo(RecipeBatchComponent::class); }

    protected function casts(): array { return ['amount' => 'integer']; }
}
