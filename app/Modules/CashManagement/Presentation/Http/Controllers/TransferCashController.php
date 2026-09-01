<?php
namespace App\Modules\CashManagement\Presentation\Http\Controllers;
use App\Http\Controllers\Controller;use App\Modules\CashManagement\Application\TransferCash;use App\Modules\CashManagement\Presentation\Http\Requests\TransferCashRequest;use Illuminate\Http\RedirectResponse;use Illuminate\Support\Carbon;
class TransferCashController extends Controller {public function __invoke(TransferCashRequest $request,TransferCash $action):RedirectResponse{$d=$request->validated();$action->execute($d['from_account_id'],$d['to_account_id'],(int)$d['amount'],Carbon::parse($d['transferred_at']),$d['reason'],$request->user());return back()->with('success','Traslado registrado.');}}
