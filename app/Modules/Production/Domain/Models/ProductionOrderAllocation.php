<?php
namespace App\Modules\Production\Domain\Models;
use App\Modules\Orders\Domain\Models\SalesOrderLine;use Illuminate\Database\Eloquent\Attributes\Fillable;use Illuminate\Database\Eloquent\Concerns\HasUlids;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[Fillable(['production_order_id','sales_order_line_id','planned_quantity','produced_quantity','reserved_quantity'])]
class ProductionOrderAllocation extends Model {use HasUlids;public function order():BelongsTo{return $this->belongsTo(ProductionOrder::class,'production_order_id');}public function salesOrderLine():BelongsTo{return $this->belongsTo(SalesOrderLine::class);}protected function casts():array{return ['planned_quantity'=>'decimal:6','produced_quantity'=>'decimal:6','reserved_quantity'=>'decimal:6'];}}
