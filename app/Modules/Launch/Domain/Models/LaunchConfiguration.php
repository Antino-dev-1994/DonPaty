<?php

namespace App\Modules\Launch\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['status', 'cutoff_at', 'attestations', 'notes', 'activated_at', 'activated_by'])]
class LaunchConfiguration extends Model
{
    use HasUlids;

    public function activator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    protected function casts(): array
    {
        return ['cutoff_at' => 'datetime', 'attestations' => 'array', 'activated_at' => 'datetime'];
    }
}
