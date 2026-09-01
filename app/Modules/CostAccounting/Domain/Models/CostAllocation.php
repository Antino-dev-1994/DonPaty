<?php
namespace App\Modules\CostAccounting\Domain\Models;
use Illuminate\Database\Eloquent\Attributes\Fillable; use Illuminate\Database\Eloquent\Concerns\HasUlids; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[Fillable(['cost_period_id','production_order_id','cost_type','base_quantity','rate','amount'])]
class CostAllocation extends Model { use HasUlids; public function period():BelongsTo{return $this->belongsTo(CostPeriod::class,'cost_period_id');} protected function casts():array{return ['base_quantity'=>'decimal:6','rate'=>'integer','amount'=>'integer'];} }
