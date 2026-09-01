<?php
namespace App\Modules\CashManagement\Presentation\Http\Controllers;
use App\Http\Controllers\Controller;use App\Modules\CashManagement\Application\OpenCashSession;use App\Modules\CashManagement\Presentation\Http\Requests\OpenCashSessionRequest;use Illuminate\Http\RedirectResponse;
class OpenCashSessionController extends Controller {public function __invoke(OpenCashSessionRequest $request,OpenCashSession $action):RedirectResponse{$d=$request->validated();$action->execute($d['financial_account_id'],(int)$d['opening_amount'],$request->user(),$d['source_account_id']??null);return back()->with('success','Caja abierta correctamente.');}}
