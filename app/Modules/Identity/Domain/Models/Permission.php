<?php

namespace App\Modules\Identity\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'label', 'group'])]
class Permission extends Model
{
    use HasUlids;

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
