<?php
namespace App\Modules\Sales\Domain\Models;
use Illuminate\Database\Eloquent\Attributes\Fillable;use Illuminate\Database\Eloquent\Concerns\HasUlids;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[Fillable(['sale_return_id','sale_line_id','quantity','returns_to_inventory','refund_amount','cost_amount','condition_notes'])]
class SaleReturnLine extends Model {use HasUlids;public function saleReturn():BelongsTo{return $this->belongsTo(SaleReturn::class);}public function saleLine():BelongsTo{return $this->belongsTo(SaleLine::class);}protected function casts():array{return ['quantity'=>'decimal:6','returns_to_inventory'=>'boolean','refund_amount'=>'integer','cost_amount'=>'integer'];}}
