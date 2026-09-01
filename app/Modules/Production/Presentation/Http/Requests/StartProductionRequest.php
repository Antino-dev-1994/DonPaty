<?php
namespace App\Modules\Production\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StartProductionRequest extends FormRequest { public function authorize():bool{return $this->user()->hasPermission('production.manage');} public function rules():array{return ['authorization_request_id'=>['nullable','ulid','exists:authorization_requests,id']];} }
