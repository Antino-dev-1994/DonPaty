<?php

namespace App\Modules\People\Domain\Models;

use App\Models\User;
use Database\Factories\PersonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use App\Modules\Customers\Domain\Models\CustomerProfile;

#[Fillable(['name', 'document_type', 'document_number', 'email', 'phone', 'notes', 'is_active'])]
class Person extends Model
{
    /** @use HasFactory<PersonFactory> */
    use HasFactory, HasUlids;

    protected static function newFactory(): PersonFactory
    {
        return PersonFactory::new();
    }

    public function classifications(): HasMany
    {
        return $this->hasMany(PersonClassification::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
