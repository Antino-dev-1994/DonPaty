<?php

namespace App\Modules\Inventory\Domain\Models;

use App\Models\User;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Inventory\Domain\Enums\InventoryDocumentStatus;
use App\Modules\Inventory\Domain\Enums\PackageConversionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['document_number', 'conversion_type', 'package_presentation_id', 'package_quantity', 'total_cost', 'source_type', 'source_id', 'status', 'created_by', 'inventory_movement_id'])]
class PackageConversion extends Model
{
    use HasUlids;

    public function packagePresentation(): BelongsTo
    {
        return $this->belongsTo(ProductPresentation::class, 'package_presentation_id');
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class, 'inventory_movement_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'conversion_type' => PackageConversionType::class,
            'status' => InventoryDocumentStatus::class,
            'package_quantity' => 'decimal:6', 'total_cost' => 'integer',
        ];
    }
}
