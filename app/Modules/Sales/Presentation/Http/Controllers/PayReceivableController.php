<?php
namespace App\Modules\Sales\Presentation\Http\Controllers;
use App\Http\Controllers\Controller;use App\Modules\Sales\Application\PayReceivable;use App\Modules\Sales\Domain\Models\Receivable;use App\Modules\Sales\Presentation\Http\Requests\PayReceivableRequest;use Illuminate\Http\RedirectResponse;use Illuminate\Support\Carbon;
class PayReceivableController extends Controller {public function __invoke(PayReceivableRequest $request,Receivable $receivable,PayReceivable $action):RedirectResponse{$d=$request->validated();$action->execute($receivable,(int)$d['amount'],$d['financial_account_id'],Carbon::parse($d['paid_at']),$request->user(),$d['reference']??null);return to_route('receivables.index')->with('success','Abono registrado correctamente.');}}
