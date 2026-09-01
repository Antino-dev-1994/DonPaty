<?php
namespace App\Modules\CostAccounting\Domain\Models;
use App\Modules\CostAccounting\Domain\Enums\UtilityType; use Illuminate\Database\Eloquent\Attributes\Fillable; use Illuminate\Database\Eloquent\Concerns\HasUlids; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo; use Illuminate\Database\Eloquent\Relations\MorphTo;
#[Fillable(['cost_period_id','cost_type','amount','source_type','source_id','effective_at'])]
class CostPoolEntry extends Model { use HasUlids; public function period():BelongsTo{return $this->belongsTo(CostPeriod::class,'cost_period_id');} public function source():MorphTo{return $this->morphTo();} protected function casts():array{return ['cost_type'=>UtilityType::class,'amount'=>'integer','effective_at'=>'datetime'];} }
