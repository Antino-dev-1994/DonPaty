<?php

namespace App\Modules\Purchasing\Domain\Models;

use App\Modules\People\Domain\Models\Person;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['person_id', 'trade_name', 'tax_identifier', 'default_payment_term_days', 'notes', 'is_active'])]
class SupplierProfile extends Model
{
    use HasUlids;

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    protected function casts(): array
    {
        return ['default_payment_term_days' => 'integer', 'is_active' => 'boolean'];
    }
}
