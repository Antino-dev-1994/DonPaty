<?php
namespace App\Modules\Production\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest; use Illuminate\Validation\Rule;
class RequestProductionAuthorizationRequest extends FormRequest { public function authorize():bool{return $this->user()->hasPermission('authorizations.request');} public function rules():array{return ['authorization_type'=>['required',Rule::in(['negative_stock','manual_labor'])],'reason'=>['required','string','max:2000']];} }
