<?php

namespace App\Modules\Production\Domain\Models;

use App\Models\User;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Inventory\Domain\Models\InventoryMovement;
use App\Modules\People\Domain\Models\Person;
use App\Modules\Production\Domain\Enums\LaborMethod;
use App\Modules\Production\Domain\Enums\ProductionStatus;
use App\Modules\Recipes\Domain\Models\RecipeVersion;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['document_number', 'recipe_version_id', 'cost_period_id', 'planned_for', 'started_at', 'completed_at', 'status', 'flour_quantity', 'expected_dough_quantity', 'actual_dough_quantity', 'waste_quantity', 'labor_method', 'responsible_person_id', 'created_by', 'negative_stock_authorization_id', 'manual_labor_authorization_id', 'consumption_movement_id', 'output_movement_id', 'ingredient_cost', 'labor_cost', 'overhead_cost', 'total_cost', 'reversed_by', 'reversed_at', 'reversal_reason', 'reversal_of_id'])]
class ProductionOrder extends Model
{
    use HasUlids;
    public function recipeVersion(): BelongsTo { return $this->belongsTo(RecipeVersion::class); }
    public function costPeriod(): BelongsTo { return $this->belongsTo(CostPeriod::class); }
    public function responsiblePerson(): BelongsTo { return $this->belongsTo(Person::class, 'responsible_person_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function plannedOutputs(): HasMany { return $this->hasMany(ProductionPlannedOutput::class); }
    public function consumptions(): HasMany { return $this->hasMany(ProductionConsumption::class); }
    public function outputs(): HasMany { return $this->hasMany(ProductionOutput::class); }
    public function incidents(): HasMany { return $this->hasMany(ProductionIncident::class); }
    public function laborEntries(): HasMany { return $this->hasMany(ProductionLaborEntry::class); }
    public function overheadAllocations(): HasMany { return $this->hasMany(ProductionOverheadAllocation::class); }
    public function orderAllocations(): HasMany { return $this->hasMany(ProductionOrderAllocation::class); }
    public function consumptionMovement(): BelongsTo { return $this->belongsTo(InventoryMovement::class, 'consumption_movement_id'); }
    public function outputMovement(): BelongsTo { return $this->belongsTo(InventoryMovement::class, 'output_movement_id'); }
    public function negativeStockAuthorization(): BelongsTo { return $this->belongsTo(AuthorizationRequest::class, 'negative_stock_authorization_id'); }

    protected function casts(): array
    {
        return ['planned_for' => 'datetime', 'started_at' => 'datetime', 'completed_at' => 'datetime', 'reversed_at' => 'datetime', 'status' => ProductionStatus::class, 'labor_method' => LaborMethod::class, 'flour_quantity' => 'decimal:6', 'expected_dough_quantity' => 'decimal:6', 'actual_dough_quantity' => 'decimal:6', 'waste_quantity' => 'decimal:6', 'ingredient_cost' => 'integer', 'labor_cost' => 'integer', 'overhead_cost' => 'integer', 'total_cost' => 'integer'];
    }
}
