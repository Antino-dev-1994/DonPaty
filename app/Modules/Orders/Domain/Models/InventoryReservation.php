<?php
namespace App\Modules\Orders\Domain\Models;
use App\Modules\Catalog\Domain\Models\ProductPresentation;use App\Modules\Orders\Domain\Enums\ReservationStatus;use Illuminate\Database\Eloquent\Attributes\Fillable;use Illuminate\Database\Eloquent\Concerns\HasUlids;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[Fillable(['presentation_id','sales_order_line_id','production_order_allocation_id','quantity','status','reserved_at','released_at'])]
class InventoryReservation extends Model {use HasUlids;public function presentation():BelongsTo{return $this->belongsTo(ProductPresentation::class);}public function orderLine():BelongsTo{return $this->belongsTo(SalesOrderLine::class,'sales_order_line_id');}protected function casts():array{return ['quantity'=>'decimal:6','status'=>ReservationStatus::class,'reserved_at'=>'datetime','released_at'=>'datetime'];}}
