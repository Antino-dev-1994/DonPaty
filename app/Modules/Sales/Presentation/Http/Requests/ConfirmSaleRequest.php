<?php
namespace App\Modules\Sales\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class ConfirmSaleRequest extends FormRequest {public function authorize():bool{return $this->user()->hasPermission('sales.create');}public function rules():array{return ['price_authorization_id'=>['nullable','ulid','exists:authorization_requests,id'],'inventory_authorization_id'=>['nullable','ulid','exists:authorization_requests,id'],'credit_authorization_id'=>['nullable','ulid','exists:authorization_requests,id']];}}
