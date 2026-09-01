<?php
namespace App\Modules\Orders\Presentation\Http\Requests;
use App\Modules\Production\Domain\Enums\LaborMethod;use Illuminate\Foundation\Http\FormRequest;use Illuminate\Validation\Rule;
class CreateProductionFromDemandRequest extends FormRequest {public function authorize():bool{return $this->user()->hasPermission('production.manage');}public function rules():array{return ['demand_ids'=>['required','array','min:1'],'demand_ids.*'=>['required','ulid','distinct','exists:production_demands,id'],'planned_for'=>['required','date'],'flour_quantity'=>['nullable','numeric','gt:0','decimal:0,6'],'labor_method'=>['required',Rule::enum(LaborMethod::class)],'responsible_person_id'=>['required','ulid','exists:people,id']];}}
