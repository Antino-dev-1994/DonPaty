<?php

namespace App\Modules\Production\Presentation\Http\Controllers;

use App\Http\Controllers\Controller; use App\Modules\Production\Domain\Models\ProductionOrder; use Illuminate\Http\Request; use Inertia\Inertia; use Inertia\Response;
class ListProductionsController extends Controller { public function __invoke(Request $request):Response { abort_unless($request->user()->hasPermission('production.view'),403); return Inertia::render('production/Index',['orders'=>ProductionOrder::query()->with(['recipeVersion.recipe:id,name','responsiblePerson:id,name'])->latest('planned_for')->paginate(25)->through(fn(ProductionOrder $order)=>[...$order->only(['id','document_number','flour_quantity','expected_dough_quantity','actual_dough_quantity','total_cost']),'planned_for'=>$order->planned_for->format('Y-m-d H:i'),'recipe'=>$order->recipeVersion->recipe->name,'version'=>$order->recipeVersion->version_number,'responsible'=>$order->responsiblePerson->name,'status'=>$order->status->value,'status_label'=>$order->status->label()]),'canManage'=>$request->user()->hasPermission('production.manage')]); } }
