<?php
namespace App\Modules\CashManagement\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class OpenCashSessionRequest extends FormRequest {public function authorize():bool{return $this->user()->hasPermission('cash.open');}public function rules():array{return ['financial_account_id'=>['required','ulid','exists:financial_accounts,id'],'opening_amount'=>['required','integer','min:0'],'source_account_id'=>['nullable','ulid','different:financial_account_id','exists:financial_accounts,id']];}}
