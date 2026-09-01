<?php
namespace App\Modules\CashManagement\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CloseCashSessionRequest extends FormRequest {public function authorize():bool{return $this->user()->hasPermission('cash.close');}public function rules():array{return ['counted_amount'=>['required','integer','min:0'],'difference_reason'=>['nullable','string','max:2000'],'authorization_request_id'=>['nullable','ulid','exists:authorization_requests,id']];}}
