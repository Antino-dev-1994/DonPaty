<?php
namespace App\Modules\Production\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreProductionIncidentRequest extends FormRequest { public function authorize():bool{return $this->user()->hasPermission('production.manage');} public function rules():array{return ['incident_type'=>['required','string','max:80'],'description'=>['required','string','max:2000'],'quantity'=>['nullable','numeric','decimal:0,6'],'amount'=>['nullable','integer','min:0']];} }
