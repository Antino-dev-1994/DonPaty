<?php

namespace App\Modules\Backups\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['status', 'database_driver', 'disk', 'path', 'size', 'sha256', 'manifest', 'verification_status', 'error_message', 'created_by', 'verified_at'])]
class BackupRun extends Model
{
    use HasUlids;

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return ['size' => 'integer', 'manifest' => 'array', 'verified_at' => 'datetime'];
    }
}
