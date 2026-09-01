<?php
namespace App\Modules\Orders\Presentation\Http\Controllers;
use App\Http\Controllers\Controller;use App\Modules\Identity\Application\CreateAuthorizationRequest;use App\Modules\Orders\Domain\Models\SalesOrder;use Illuminate\Http\RedirectResponse;use Illuminate\Http\Request;
class RequestOrderPriceAuthorizationController extends Controller {public function __invoke(Request $request,SalesOrder $order,CreateAuthorizationRequest $action):RedirectResponse{abort_unless($request->user()->hasPermission('authorizations.request'),403);$data=$request->validate(['reason'=>['required','string','max:2000']]);$action->execute('orders.price-below-minimum','prices.authorize-below-minimum',$order,$request->user(),$data['reason']);return back()->with('success','Autorización de precio solicitada.');}}
