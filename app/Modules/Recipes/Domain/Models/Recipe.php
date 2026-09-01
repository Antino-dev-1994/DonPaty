<?php

namespace App\Modules\Recipes\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'name', 'description', 'is_active'])]
class Recipe extends Model
{
    use HasUlids, SoftDeletes;
    public function versions(): HasMany { return $this->hasMany(RecipeVersion::class); }
    protected function casts(): array { return ['is_active' => 'boolean']; }
}
