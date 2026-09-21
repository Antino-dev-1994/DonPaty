<?php

namespace App\Modules\Recipes\Presentation\Http\Requests;

use App\Modules\Recipes\Domain\Enums\IngredientRole;
use App\Modules\Recipes\Domain\Enums\RecipeBatchComponentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRecipeRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasPermission('recipes.manage'); }
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('recipes', 'code')->ignore($this->route('recipe'))],
            'name' => ['required', 'string', 'max:160'], 'description' => ['nullable', 'string', 'max:2000'], 'is_active' => ['required', 'boolean'],
            'reference_flour_quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,6'], 'reference_flour_unit_id' => ['required', 'ulid', 'exists:units,id'],
            'expected_dough_yield' => ['required', 'numeric', 'gt:0', 'decimal:0,6'], 'yield_unit_id' => ['required', 'ulid', 'exists:units,id'],
            'expected_waste_percentage' => ['required', 'numeric', 'min:0', 'max:100', 'decimal:0,4'], 'instructions' => ['nullable', 'string', 'max:10000'],
            'ingredients' => ['required', 'array', 'min:2'], 'ingredients.*.item_id' => ['required', 'ulid', 'distinct', 'exists:items,id'],
            'ingredients.*.presentation_id' => ['nullable', 'ulid', 'exists:product_presentations,id'], 'ingredients.*.ingredient_role' => ['required', Rule::enum(IngredientRole::class)],
            'ingredients.*.quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,6'], 'ingredients.*.unit_id' => ['required', 'ulid', 'exists:units,id'],
            'ingredients.*.baker_percentage' => ['nullable', 'numeric', 'min:0', 'decimal:0,4'], 'ingredients.*.allows_substitution' => ['required', 'boolean'],
            'batch_components' => ['sometimes', 'array'],
            'batch_components.*.type' => ['required', Rule::enum(RecipeBatchComponentType::class)],
            'batch_components.*.label' => ['required', 'string', 'max:160'],
            'batch_components.*.item_id' => ['nullable', 'ulid', 'exists:items,id'],
            'batch_components.*.quantity_per_batch' => ['nullable', 'numeric', 'gt:0', 'decimal:0,6'],
            'batch_components.*.unit_id' => ['nullable', 'ulid', 'exists:units,id'],
            'batch_components.*.amount_per_batch' => ['nullable', 'integer', 'min:0'],
            'compatible_products' => ['required', 'array', 'min:1'], 'compatible_products.*.presentation_id' => ['required', 'ulid', 'distinct', 'exists:product_presentations,id'],
            'compatible_products.*.dough_weight_per_unit' => ['required', 'numeric', 'gt:0', 'decimal:0,6'], 'compatible_products.*.dough_weight_unit_id' => ['required', 'ulid', 'exists:units,id'],
            'compatible_products.*.baking_loss_percentage' => ['required', 'numeric', 'min:0', 'max:100', 'decimal:0,4'], 'compatible_products.*.cost_weight_factor' => ['required', 'numeric', 'gt:0', 'decimal:0,6'],
            'compatible_products.*.finishing_components' => ['sometimes', 'array'], 'compatible_products.*.finishing_components.*.item_id' => ['required', 'ulid', 'exists:items,id'],
            'compatible_products.*.finishing_components.*.quantity_per_unit' => ['required', 'numeric', 'gt:0', 'decimal:0,6'], 'compatible_products.*.finishing_components.*.unit_id' => ['required', 'ulid', 'exists:units,id'],
        ];
    }
}
