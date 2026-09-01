<?php
namespace App\Modules\Production\Domain\Models;
use App\Modules\People\Domain\Models\Person; use App\Modules\Production\Domain\Enums\LaborMethod; use Illuminate\Database\Eloquent\Attributes\Fillable; use Illuminate\Database\Eloquent\Concerns\HasUlids; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[Fillable(['production_order_id','person_id','method','hours','hourly_rate','flour_rate','manual_amount','total_amount','reason'])]
class ProductionLaborEntry extends Model { use HasUlids; public function order():BelongsTo{return $this->belongsTo(ProductionOrder::class,'production_order_id');} public function person():BelongsTo{return $this->belongsTo(Person::class);} protected function casts():array{return ['method'=>LaborMethod::class,'hours'=>'decimal:4','hourly_rate'=>'integer','flour_rate'=>'integer','manual_amount'=>'integer','total_amount'=>'integer'];} }
