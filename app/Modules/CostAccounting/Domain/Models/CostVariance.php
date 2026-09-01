<?php
namespace App\Modules\CostAccounting\Domain\Models;
use Illuminate\Database\Eloquent\Attributes\Fillable; use Illuminate\Database\Eloquent\Concerns\HasUlids; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[Fillable(['cost_period_id','cost_type','actual_amount','allocated_amount','variance_amount'])]
class CostVariance extends Model { use HasUlids; public function period():BelongsTo{return $this->belongsTo(CostPeriod::class,'cost_period_id');} protected function casts():array{return ['actual_amount'=>'integer','allocated_amount'=>'integer','variance_amount'=>'integer'];} }
