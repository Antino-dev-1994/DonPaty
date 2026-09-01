<?php

namespace App\Modules\Production\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\People\Domain\Models\Person;
use App\Modules\Production\Domain\Enums\ProductionStatus;
use App\Modules\Production\Domain\Models\ProductionOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateCompleteProductionController extends Controller
{
    public function __invoke(Request $request, ProductionOrder $production): Response
    {
        abort_unless($request->user()->hasPermission('production.complete') && $production->status === ProductionStatus::InProgress, 403);
        $production->load(['consumptions.item:id,name', 'consumptions.unit:id,code', 'plannedOutputs.compatibleProduct.presentation.item:id,name', 'recipeVersion.compatibleProducts.presentation.item:id,name']);
        $manualAuthorizations = AuthorizationRequest::query()->where('resource_type', $production->getMorphClass())->where('resource_id', $production->id)->where('approval_permission', 'production.authorize-manual-labor')->get()->filter->isUsable()->values();
        return Inertia::render('production/Complete', [
            'order' => [...$production->only(['id','document_number','expected_dough_quantity','flour_quantity']), 'labor_method'=>$production->labor_method->value, 'consumptions'=>$production->consumptions->map(fn($line)=>['id'=>$line->id,'item'=>$line->item->name,'calculated_quantity'=>$line->calculated_quantity,'unit'=>$line->unit->code]), 'products'=>$production->recipeVersion->compatibleProducts->map(fn($product)=>['id'=>$product->id,'name'=>"{$product->presentation->item->name} — {$product->presentation->name}"]), 'planned_outputs'=>$production->plannedOutputs->map(fn($line)=>['compatible_product_id'=>$line->recipe_compatible_product_id,'quantity'=>$line->planned_quantity,'dough_quantity'=>$line->planned_dough_quantity])],
            'people'=>Person::query()->where('is_active',true)->orderBy('name')->get(['id','name']), 'responsiblePersonId'=>$production->responsible_person_id, 'manualAuthorizations'=>$manualAuthorizations->map(fn($auth)=>['id'=>$auth->id,'reason'=>$auth->reason]), 'now'=>now()->format('Y-m-d\TH:i'),
        ]);
    }
}
