<?php

namespace App\Modules\CostAccounting\Presentation\Http\Requests;

use App\Modules\CostAccounting\Domain\Enums\UtilityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpenCostPeriodRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasPermission('cost-periods.manage'); }
    public function rules(): array
    {
        return ['year'=>['required','integer','min:2020','max:2100'],'month'=>['required','integer','between:1,12'],'utilities'=>['required','array','size:2'],'utilities.*.utility_type'=>['required','distinct',Rule::enum(UtilityType::class)],'utilities.*.billed_from'=>['required','date'],'utilities.*.billed_to'=>['required','date','after_or_equal:utilities.*.billed_from'],'utilities.*.paid_at'=>['required','date'],'utilities.*.total_amount'=>['required','integer','min:0'],'utilities.*.business_percentage'=>['required','numeric','min:0','max:100','decimal:0,4'],'utilities.*.household_percentage'=>['required','numeric','min:0','max:100','decimal:0,4'],'utilities.*.physical_consumption'=>['nullable','numeric','min:0','decimal:0,6'],'utilities.*.reference'=>['nullable','string','max:160'],'utilities.*.manual_rate'=>['nullable','integer','min:0'],'utilities.*.override_reason'=>['nullable','string','max:2000']];
    }
}
