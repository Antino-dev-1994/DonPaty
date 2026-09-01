<?php

namespace App\Modules\Recipes\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScaleRecipeRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasPermission('recipes.view'); }
    public function rules(): array { return ['flour_quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,6'], 'flour_unit_id' => ['required', 'ulid', 'exists:units,id']]; }
}
