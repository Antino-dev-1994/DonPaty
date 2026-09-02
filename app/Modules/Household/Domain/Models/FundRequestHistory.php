<?php

namespace App\Modules\Household\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['fund_request_id', 'from_status', 'to_status', 'changed_by', 'changed_at', 'notes'])]
class FundRequestHistory extends Model
{
    use HasUlids;
    public function request(): BelongsTo { return $this->belongsTo(FundRequest::class, 'fund_request_id'); }
    public function changer(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
    protected function casts(): array { return ['changed_at' => 'datetime']; }
}
