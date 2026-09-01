<?php
namespace App\Modules\Orders\Presentation\Http\Controllers;
use App\Http\Controllers\Controller;use App\Modules\Orders\Application\AddOrderAdvance;use App\Modules\Orders\Domain\Models\SalesOrder;use App\Modules\Orders\Presentation\Http\Requests\AddOrderAdvanceRequest;use Illuminate\Http\RedirectResponse;use Illuminate\Support\Carbon;
class AddOrderAdvanceController extends Controller {public function __invoke(AddOrderAdvanceRequest $request,SalesOrder $order,AddOrderAdvance $action):RedirectResponse{$d=$request->validated();$action->execute($order,(int)$d['amount'],$d['financial_account_id'],Carbon::parse($d['paid_at']),$request->user(),$d['reference']??null);return back()->with('success','Anticipo registrado.');}}
