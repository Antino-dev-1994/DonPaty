<?php

namespace App\Modules\Audit\Domain\Models;

use App\Models\User;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['actor_user_id', 'action', 'resource_type', 'resource_id', 'authorization_request_id', 'before_data', 'after_data', 'ip_address', 'correlation_id'])]
class AuditEvent extends Model
{
    use HasUlids;

    public const UPDATED_AT = null;

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function authorizationRequest(): BelongsTo
    {
        return $this->belongsTo(AuthorizationRequest::class);
    }

    protected function casts(): array
    {
        return [
            'before_data' => 'array',
            'after_data' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
