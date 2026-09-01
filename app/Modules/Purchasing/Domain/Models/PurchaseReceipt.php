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

#[Fillable(['document_number', 'purchase_id', 'received_at', 'status', 'received_by', 'inventory_movement_id', 'notes'])]
class PurchaseReceipt extends Model
{
    use HasUlids;

    public function purchase(): BelongsTo { return $this->belongsTo(Purchase::class); }
    public function lines(): HasMany { return $this->hasMany(PurchaseReceiptLine::class); }
    public function receiver(): BelongsTo { return $this->belongsTo(User::class, 'received_by'); }
    public function inventoryMovement(): BelongsTo { return $this->belongsTo(InventoryMovement::class); }

    protected function casts(): array
    {
        return ['received_at' => 'datetime', 'status' => PurchasingDocumentStatus::class];
    }
}
