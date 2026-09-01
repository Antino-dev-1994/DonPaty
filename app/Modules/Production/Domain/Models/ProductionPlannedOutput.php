<?php
namespace App\Modules\Production\Domain\Models;
use App\Modules\Catalog\Domain\Models\ProductPresentation; use App\Modules\Recipes\Domain\Models\RecipeCompatibleProduct; use Illuminate\Database\Eloquent\Attributes\Fillable; use Illuminate\Database\Eloquent\Concerns\HasUlids; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[Fillable(['production_order_id','recipe_compatible_product_id','presentation_id','planned_quantity','planned_dough_quantity'])]
class ProductionPlannedOutput extends Model { use HasUlids; public function order():BelongsTo{return $this->belongsTo(ProductionOrder::class,'production_order_id');} public function compatibleProduct():BelongsTo{return $this->belongsTo(RecipeCompatibleProduct::class);} public function presentation():BelongsTo{return $this->belongsTo(ProductPresentation::class);} protected function casts():array{return ['planned_quantity'=>'decimal:6','planned_dough_quantity'=>'decimal:6'];} }
