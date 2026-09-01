<?php
namespace App\Modules\Orders\Presentation\Http\Controllers;
use App\Http\Controllers\Controller;use App\Modules\Orders\Application\CancelSalesOrder;use App\Modules\Orders\Domain\Models\SalesOrder;use App\Modules\Orders\Presentation\Http\Requests\CancelOrderRequest;use Illuminate\Http\RedirectResponse;
class CancelSalesOrderController extends Controller {public function __invoke(CancelOrderRequest $request,SalesOrder $order,CancelSalesOrder $action):RedirectResponse{$action->execute($order,$request->user(),$request->validated('reason'));return back()->with('success','Pedido cancelado y reservas liberadas.');}}
