<?php

namespace App\Modules\Finance\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['payment_id', 'allocatable_type', 'allocatable_id', 'amount'])]
class PaymentAllocation extends Model
{
    use HasUlids;
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function allocatable(): MorphTo { return $this->morphTo(); }
    protected function casts(): array { return ['amount' => 'integer']; }
}
