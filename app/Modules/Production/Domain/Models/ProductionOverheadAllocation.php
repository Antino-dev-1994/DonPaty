<?php
namespace App\Modules\Production\Domain\Models;
use App\Modules\CostAccounting\Domain\Enums\UtilityType; use App\Modules\CostAccounting\Domain\Models\OverheadRate; use Illuminate\Database\Eloquent\Attributes\Fillable; use Illuminate\Database\Eloquent\Concerns\HasUlids; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[Fillable(['production_order_id','overhead_rate_id','cost_type','base_quantity','rate','amount'])]
class ProductionOverheadAllocation extends Model { use HasUlids; public function order():BelongsTo{return $this->belongsTo(ProductionOrder::class,'production_order_id');} public function overheadRate():BelongsTo{return $this->belongsTo(OverheadRate::class);} protected function casts():array{return ['cost_type'=>UtilityType::class,'base_quantity'=>'decimal:6','rate'=>'integer','amount'=>'integer'];} }
