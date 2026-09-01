<?php

namespace App\Modules\Recipes\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublishRecipeVersionRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasPermission('recipes.activate-version'); }
    public function rules(): array { return ['effective_from' => ['required', 'date']]; }
}
