<?php
namespace App\Modules\Production\Presentation\Http\Controllers;
use App\Http\Controllers\Controller; use App\Modules\Production\Application\RequestProductionAuthorization; use App\Modules\Production\Domain\Models\ProductionOrder; use App\Modules\Production\Presentation\Http\Requests\RequestProductionAuthorizationRequest; use Illuminate\Http\RedirectResponse;
class RequestProductionAuthorizationController extends Controller { public function __invoke(RequestProductionAuthorizationRequest $request,ProductionOrder $production,RequestProductionAuthorization $action):RedirectResponse { $data=$request->validated();$action->execute($production,$data['authorization_type'],$data['reason'],$request->user());return back()->with('success','Solicitud de autorización creada.'); } }
