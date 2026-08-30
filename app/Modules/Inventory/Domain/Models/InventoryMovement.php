<?php

namespace App\Modules\Inventory\Domain\Models;

use App\Models\User;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Inventory\Domain\Enums\InventoryDocumentStatus;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['document_number', 'movement_type', 'effective_at', 'source_type', 'source_id', 'status', 'notes', 'created_by', 'authorization_request_id', 'reversal_of_id'])]
class InventoryMovement extends Model
{
    use HasUlids;

    public function lines(): HasMany
    {
        return $this->hasMany(InventoryMovementLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function authorizationRequest(): BelongsTo
    {
        return $this->belongsTo(AuthorizationRequest::class);
    }

    protected function casts(): array
    {
        return [
            'movement_type' => InventoryMovementType::class,
            'status' => InventoryDocumentStatus::class,
            'effective_at' => 'datetime',
        ];
    }
}
