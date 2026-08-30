<?php

namespace App\Modules\Inventory\Domain\Models;

use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['presentation_id', 'inventory_movement_line_id', 'authorization_request_id', 'negative_quantity', 'estimated_unit_cost', 'status', 'regularized_at'])]
class NegativeStockIncident extends Model
{
    use HasUlids;

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(ProductPresentation::class);
    }

    public function movementLine(): BelongsTo
    {
        return $this->belongsTo(InventoryMovementLine::class);
    }

    public function authorizationRequest(): BelongsTo
    {
        return $this->belongsTo(AuthorizationRequest::class);
    }

    protected function casts(): array
    {
        return [
            'negative_quantity' => 'decimal:6', 'estimated_unit_cost' => 'integer',
            'regularized_at' => 'datetime',
        ];
    }
}
