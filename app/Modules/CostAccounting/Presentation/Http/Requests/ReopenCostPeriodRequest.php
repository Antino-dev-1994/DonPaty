<?php

namespace App\Modules\CostAccounting\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReopenCostPeriodRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasPermission('cost-periods.reopen'); }
    public function rules(): array { return ['reason' => ['required', 'string', 'max:2000']]; }
}
