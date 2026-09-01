<?php
namespace App\Modules\CashManagement\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class TransferCashRequest extends FormRequest {public function authorize():bool{return $this->user()->hasPermission('cash.operate');}public function rules():array{return ['from_account_id'=>['required','ulid','exists:financial_accounts,id'],'to_account_id'=>['required','ulid','different:from_account_id','exists:financial_accounts,id'],'amount'=>['required','integer','gt:0'],'transferred_at'=>['required','date'],'reason'=>['required','string','max:2000']];}}
