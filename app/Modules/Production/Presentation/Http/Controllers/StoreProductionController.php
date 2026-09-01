<?php

namespace App\Modules\Production\Presentation\Http\Controllers;

use App\Http\Controllers\Controller; use App\Modules\Production\Application\Data\PlannedOutputData; use App\Modules\Production\Application\Data\PlanProductionData; use App\Modules\Production\Application\PlanProduction; use App\Modules\Production\Domain\Enums\LaborMethod; use App\Modules\Production\Presentation\Http\Requests\StoreProductionRequest; use Illuminate\Http\RedirectResponse; use Illuminate\Support\Carbon;
class StoreProductionController extends Controller { public function __invoke(StoreProductionRequest $request,PlanProduction $action):RedirectResponse { $data=$request->validated();$order=$action->execute(new PlanProductionData($data['recipe_version_id'],Carbon::parse($data['planned_for']),(string)$data['flour_quantity'],LaborMethod::from($data['labor_method']),$data['responsible_person_id'],$request->user(),array_map(PlannedOutputData::fromArray(...),$data['outputs'])));return to_route('production.show',$order)->with('success','Producción planificada correctamente.'); } }
