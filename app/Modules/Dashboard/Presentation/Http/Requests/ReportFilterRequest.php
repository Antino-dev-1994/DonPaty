<?php

namespace App\Modules\Dashboard\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->hasPermission('reports.view-operational')
            || $user->hasPermission('reports.view-financial')
            || $user->hasPermission('household.view-own')
            || $user->hasPermission('household.view-all');
    }

    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'presentation_id' => ['nullable', 'ulid', 'exists:product_presentations,id'],
            'person_id' => ['nullable', 'ulid', 'exists:people,id'],
        ];
    }

    /** @return list<callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->filled(['from', 'to']) && Carbon::parse($this->input('from'))->diffInDays(Carbon::parse($this->input('to'))) > 366) {
                $validator->errors()->add('to', 'El rango no puede superar 366 días.');
            }
        }];
    }
}
