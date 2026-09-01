<?php
namespace App\Modules\Sales\Domain\Models;
use App\Modules\People\Domain\Models\Person;use App\Modules\Sales\Domain\Enums\ReceivableStatus;use Illuminate\Database\Eloquent\Attributes\Fillable;use Illuminate\Database\Eloquent\Concerns\HasUlids;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;use Illuminate\Database\Eloquent\Relations\MorphTo;
#[Fillable(['document_number','person_id','source_type','source_id','issued_at','due_at','original_amount','paid_amount','credited_amount','balance_amount','status'])]
class Receivable extends Model {use HasUlids;public function person():BelongsTo{return $this->belongsTo(Person::class);}public function source():MorphTo{return $this->morphTo();}protected function casts():array{return ['issued_at'=>'date','due_at'=>'date','original_amount'=>'integer','paid_amount'=>'integer','credited_amount'=>'integer','balance_amount'=>'integer','status'=>ReceivableStatus::class];}}
