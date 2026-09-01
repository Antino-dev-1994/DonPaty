<?php

namespace App\Modules\Customers\Domain\Models;

use App\Modules\People\Domain\Models\Person;
use App\Modules\Pricing\Domain\Models\PriceList;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['person_id', 'default_price_list_id', 'credit_limit', 'default_payment_term_days', 'delivery_notes', 'is_active'])]
class CustomerProfile extends Model
{
    use HasUlids;

    public function person(): BelongsTo { return $this->belongsTo(Person::class); }
    public function defaultPriceList(): BelongsTo { return $this->belongsTo(PriceList::class, 'default_price_list_id'); }
    protected function casts(): array { return ['credit_limit' => 'integer', 'default_payment_term_days' => 'integer', 'is_active' => 'boolean']; }
}
