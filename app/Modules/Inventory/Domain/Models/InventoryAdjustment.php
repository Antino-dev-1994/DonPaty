<?php

namespace App\Modules\Inventory\Domain\Models;

use App\Models\User;
use App\Modules\Inventory\Domain\Enums\InventoryDocumentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['document_number', 'adjustment_type', 'effective_at', 'status', 'reason', 'created_by', 'confirmed_by', 'inventory_movement_id'])]
class InventoryAdjustment extends Model
{
    use HasUlids;

    public function lines(): HasMany
    {
        return $this->hasMany(InventoryAdjustmentLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class, 'inventory_movement_id');
    }

    protected function casts(): array
    {
        return ['status' => InventoryDocumentStatus::class, 'effective_at' => 'datetime'];
    }
}
