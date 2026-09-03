<?php

namespace App\Modules\Production\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Production\Application\ProductionAvailability;
use App\Modules\Production\Domain\Enums\ProductionStatus;
use App\Modules\Production\Domain\Models\ProductionOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowProductionController extends Controller
{
    public function __invoke(Request $request, ProductionOrder $production, ProductionAvailability $availability): Response
    {
        abort_unless($request->user()->hasPermission('production.view'), 403);
        $canViewCosts = $request->user()->hasPermission('reports.view-financial');
        $production->load(['recipeVersion.recipe:id,name', 'costPeriod', 'responsiblePerson:id,name', 'consumptions.item:id,name', 'consumptions.unit:id,code', 'consumptions.presentation:id,name,stock_unit_id', 'consumptions.presentation.stockUnit:id,code', 'plannedOutputs.presentation.item:id,name', 'outputs.presentation.item:id,name', 'incidents.recorder:id,name', 'laborEntries.person:id,name', 'overheadAllocations']);
        $authorizations = AuthorizationRequest::query()->where('resource_type', $production->getMorphClass())->where('resource_id', $production->id)->latest()->get();

        return Inertia::render('production/Show', [
            'order' => [
                ...$production->only(['id', 'document_number', 'flour_quantity', 'expected_dough_quantity', 'actual_dough_quantity', 'waste_quantity', 'reversal_reason']),
                'ingredient_cost' => $canViewCosts ? $production->ingredient_cost : null,
                'labor_cost' => $canViewCosts ? $production->labor_cost : null,
                'overhead_cost' => $canViewCosts ? $production->overhead_cost : null,
                'total_cost' => $canViewCosts ? $production->total_cost : null,
                'planned_for' => $production->planned_for->format('Y-m-d H:i'), 'started_at' => $production->started_at?->format('Y-m-d H:i'), 'completed_at' => $production->completed_at?->format('Y-m-d H:i'),
                'status' => $production->status->value, 'status_label' => $production->status->label(), 'labor_method' => $production->labor_method->value, 'labor_method_label' => $production->labor_method->label(),
                'recipe' => $production->recipeVersion->recipe->name, 'recipe_version' => $production->recipeVersion->version_number, 'period' => $production->costPeriod->label(), 'responsible' => $production->responsiblePerson->name,
                'consumptions' => $production->consumptions->map(fn($line)=>[...$line->only(['id','calculated_quantity','actual_quantity','difference_reason']),'unit_cost'=>$canViewCosts ? $line->unit_cost : null,'total_cost'=>$canViewCosts ? $line->total_cost : null,'item'=>$line->item->name,'presentation'=>$line->presentation->name,'unit'=>$line->unit->code]),
                'planned_outputs' => $production->plannedOutputs->map(fn($line)=>['id'=>$line->id,'product'=>"{$line->presentation->item->name} — {$line->presentation->name}",'quantity'=>$line->planned_quantity,'dough_quantity'=>$line->planned_dough_quantity]),
                'outputs' => $production->outputs->map(fn($line)=>['id'=>$line->id,'product'=>"{$line->presentation->item->name} — {$line->presentation->name}",'quantity'=>$line->quantity,'dough_quantity'=>$line->dough_quantity,'allocated_cost'=>$canViewCosts ? $line->allocated_cost : null,'unit_cost'=>$canViewCosts ? $line->unit_cost : null]),
                'incidents' => $production->incidents->map(fn($incident)=>[...$incident->only(['id','incident_type','description','quantity','amount']),'recorded_at'=>$incident->recorded_at->format('Y-m-d H:i'),'recorder'=>$incident->recorder->name]),
                'labor_entries' => $production->laborEntries->map(fn($line)=>[...$line->only(['id','hours','hourly_rate','flour_rate','manual_amount','total_amount','reason']),'person'=>$line->person?->name,'method'=>$line->method->label()]),
            ],
            'availability' => $production->status === ProductionStatus::Planned ? $availability->execute($production) : [],
            'authorizations' => $authorizations->map(fn($authorization)=>['id'=>$authorization->id,'permission'=>$authorization->approval_permission,'status'=>$authorization->status->value,'usable'=>$authorization->isUsable(),'reason'=>$authorization->reason]),
            'canManage' => $request->user()->hasPermission('production.manage'), 'canComplete' => $request->user()->hasPermission('production.complete'), 'canReverse' => $request->user()->hasPermission('production.reverse'), 'canRequestAuthorization' => $request->user()->hasPermission('authorizations.request'), 'canViewCosts' => $canViewCosts,
        ]);
    }
}
