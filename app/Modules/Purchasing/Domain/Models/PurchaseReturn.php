<?php

namespace App\Modules\Purchasing\Domain\Models;

use App\Models\User;
use App\Modules\Inventory\Domain\Models\InventoryMovement;
use App\Modules\Purchasing\Domain\Enums\PurchasingDocumentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['document_number', 'purchase_id', 'returned_at', 'status', 'total_amount', 'inventory_movement_id', 'created_by', 'reason'])]
class PurchaseReturn extends Model
{
    use HasUlids;

    public function purchase(): BelongsTo { return $this->belongsTo(Purchase::class); }
    public function lines(): HasMany { return $this->hasMany(PurchaseReturnLine::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function inventoryMovement(): BelongsTo { return $this->belongsTo(InventoryMovement::class); }

    protected function casts(): array
    {
        return ['returned_at' => 'datetime', 'status' => PurchasingDocumentStatus::class, 'total_amount' => 'integer'];
    }
}
