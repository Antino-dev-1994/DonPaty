<?php

namespace App\Modules\Production\Domain\Services;

use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Recipes\Domain\Models\RecipeIngredient;
use DomainException;

class ConsumablePresentationResolver
{
    public function resolve(RecipeIngredient $ingredient, Unit $recipeUnit): ProductPresentation
    {
        if ($ingredient->presentation_id) {
            $presentation = ProductPresentation::query()->with('stockUnit')->findOrFail($ingredient->presentation_id);
            if (! $presentation->is_active || ! $presentation->is_stockable || $presentation->stockUnit->dimension !== $recipeUnit->dimension) throw new DomainException('La presentación configurada no puede consumirse en la unidad de la receta.');
            return $presentation;
        }

        return $this->resolveForItem($ingredient->item_id, $recipeUnit, $ingredient->item->name);
    }

    public function resolveForItem(string $itemId, Unit $unit, string $itemName): ProductPresentation
    {
        $presentation = ProductPresentation::query()->with('stockUnit')->where('item_id', $itemId)->where('is_active', true)->where('is_stockable', true)
            ->get()->first(fn (ProductPresentation $candidate) => $candidate->stockUnit->dimension === $unit->dimension);
        if (! $presentation) throw new DomainException("El ingrediente {$itemName} necesita una presentación inventariable compatible.");

        return $presentation;
    }
}
