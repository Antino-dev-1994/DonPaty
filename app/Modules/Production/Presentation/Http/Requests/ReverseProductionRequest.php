<?php
namespace App\Modules\Production\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class ReverseProductionRequest extends FormRequest { public function authorize():bool{return $this->user()->hasPermission('production.reverse');} public function rules():array{return ['reason'=>['required','string','max:2000'],'authorization_request_id'=>['nullable','ulid','exists:authorization_requests,id']];} }
