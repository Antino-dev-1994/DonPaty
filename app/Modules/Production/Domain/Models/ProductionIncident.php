<?php
namespace App\Modules\Production\Domain\Models;
use App\Models\User; use Illuminate\Database\Eloquent\Attributes\Fillable; use Illuminate\Database\Eloquent\Concerns\HasUlids; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[Fillable(['production_order_id','incident_type','description','quantity','amount','recorded_by','recorded_at'])]
class ProductionIncident extends Model { use HasUlids; public function order():BelongsTo{return $this->belongsTo(ProductionOrder::class,'production_order_id');} public function recorder():BelongsTo{return $this->belongsTo(User::class,'recorded_by');} protected function casts():array{return ['quantity'=>'decimal:6','amount'=>'integer','recorded_at'=>'datetime'];} }
