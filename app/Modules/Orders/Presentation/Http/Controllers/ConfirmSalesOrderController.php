<?php
namespace App\Modules\Orders\Presentation\Http\Controllers;
use App\Http\Controllers\Controller;use App\Modules\Orders\Application\ConfirmSalesOrder;use App\Modules\Orders\Domain\Models\SalesOrder;use Illuminate\Http\RedirectResponse;use Illuminate\Http\Request;
class ConfirmSalesOrderController extends Controller {public function __invoke(Request $request,SalesOrder $order,ConfirmSalesOrder $action):RedirectResponse{abort_unless($request->user()->hasPermission('orders.manage'),403);$action->execute($order,$request->user());return back()->with('success','Pedido confirmado y reservas actualizadas.');}}
