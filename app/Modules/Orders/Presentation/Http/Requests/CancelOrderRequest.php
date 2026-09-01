<?php
namespace App\Modules\Orders\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CancelOrderRequest extends FormRequest {public function authorize():bool{return $this->user()->hasPermission('orders.manage');}public function rules():array{return ['reason'=>['required','string','max:2000']];}}
