<?php

namespace App\Modules\Identity\Domain\Models;

use App\Models\User;
use App\Modules\Identity\Domain\Enums\AuthorizationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['operation_type', 'approval_permission', 'resource_type', 'resource_id', 'requested_by', 'approved_by', 'reason', 'decision_notes', 'status', 'expires_at', 'decided_at', 'used_at'])]
class AuthorizationRequest extends Model
{
    use HasUlids;

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isUsable(): bool
    {
        return $this->status === AuthorizationStatus::Approved
            && $this->used_at === null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    protected function casts(): array
    {
        return [
            'status' => AuthorizationStatus::class,
            'expires_at' => 'datetime',
            'decided_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }
}
