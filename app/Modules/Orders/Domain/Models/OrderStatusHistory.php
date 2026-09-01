<?php
namespace App\Modules\Orders\Domain\Models;
use App\Models\User;use Illuminate\Database\Eloquent\Attributes\Fillable;use Illuminate\Database\Eloquent\Concerns\HasUlids;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[Fillable(['sales_order_id','from_status','to_status','changed_by','changed_at','notes'])]
class OrderStatusHistory extends Model {use HasUlids;public function order():BelongsTo{return $this->belongsTo(SalesOrder::class,'sales_order_id');}public function changer():BelongsTo{return $this->belongsTo(User::class,'changed_by');}protected function casts():array{return ['changed_at'=>'datetime'];}}
